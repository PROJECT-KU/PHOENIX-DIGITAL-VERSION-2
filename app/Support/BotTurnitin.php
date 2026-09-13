<?php

namespace App\Support;

use App\Actions\Jasa\SelesaikanPesananJasa;
use App\Mail\JasaHasilMail;
use App\Models\OrderUpload;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Bot Turnitin — pengecekan plagiasi dikerjakan skrip Tampermonkey di Chrome
 * admin (public/bot/phoenix-turnitin.user.js) lewat submitin.id, paket Standard.
 *
 * Kenapa di Chrome admin, bukan di server: hosting mematikan proc_open jadi
 * peramban tidak bisa dijalankan di sana, dan VPS / komputer yang menyala terus
 * belum tersedia. Skripnya yang AKTIF bertanya ke Phoenix, jadi Phoenix cukup
 * menyediakan antrean lewat API bertoken.
 *
 * Alurnya (bot_status):
 *   NULL ─ambilTugas()→ diambil ─tandaiTerkirim(kode)→ menunggu_hasil
 *        ─simpanHasil(kode, pdf)→ selesai | perlu_dilengkapi
 *   diambil / menunggu_hasil ─gagal()→ gagal  (masuk kartu dashboard)
 *
 * Tiga pengaman supaya laporan TIDAK tertukar ke pesanan lain:
 *   1. Hanya SATU tugas aktif pada satu waktu (ambilTugas mengembalikan tugas
 *      yang sedang berjalan, bukan tugas baru).
 *   2. Berkas yang diunggah ke submitin diberi penanda unik per unggahan
 *      (namaBerkas); skrip memastikan penanda itu terlihat di halaman status.
 *   3. Hasil hanya diterima bila kode order submitin yang dikirim SAMA dengan
 *      kode yang tercatat untuk unggahan ini saat terkirim.
 */
class BotTurnitin
{
    public const DIAMBIL = 'diambil';

    public const MENUNGGU_HASIL = 'menunggu_hasil';

    public const SELESAI = 'selesai';

    public const GAGAL = 'gagal';

    public const PERLU_DILENGKAPI = 'perlu_dilengkapi';

    public const MANUAL = 'manual';

    /** Tanpa kabar dari bot selama ini, tugas aktif dianggap macet. */
    public const MACET_MENIT = 15;

    /** Detak lebih tua dari ini = bot dianggap tidak aktif (tab tertutup). */
    public const AKTIF_MENIT = 3;

    private const KUNCI_TOKEN = 'bot_turnitin_token_hash';

    private const KUNCI_JEDA = 'bot_turnitin_dijeda';

    private const KUNCI_DETAK = 'bot_turnitin_detak_at';

    private const KUNCI_KUOTA = 'bot_turnitin_kuota_habis';

    private const KUNCI_MASALAH = 'bot_turnitin_masalah';

    private static ?bool $skemaSiap = null;

    /**
     * Kolom bot sudah ada di database?
     *
     * Deploy Phoenix menjalankan SQL secara manual SESUDAH kode terambil. Kode
     * yang langsung memakai kolom baru membuat halaman galat pada jeda itu
     * (pernah terjadi: kolom product_reviews.jenis, 11 Sep 2026). Semua titik
     * yang menyentuh kolom bot memeriksa ini lebih dulu — terutama halaman
     * unggah customer, yang tidak boleh ikut rusak karena fitur admin.
     */
    public static function skemaSiap(): bool
    {
        return self::$skemaSiap ??= Schema::hasColumn('order_uploads', 'bot_status');
    }

    /** Dipakai uji: kosongkan hafalan, atau paksa keadaan "kolom belum ada". */
    public static function lupakanSkema(?bool $paksa = null): void
    {
        self::$skemaSiap = $paksa;
    }

    /* ------------------------------------------------------------------
     | Token, jeda, detak, kuota
     * ------------------------------------------------------------------ */

    /** Buat token baru (menggantikan yang lama). Nilai polos hanya dikembalikan sekali. */
    public static function buatToken(): string
    {
        $token = 'pdbot_'.Str::random(40);
        Setting::set(self::KUNCI_TOKEN, hash('sha256', $token));

        return $token;
    }

    public static function sudahDipasang(): bool
    {
        return (bool) Setting::get(self::KUNCI_TOKEN);
    }

    public static function tokenCocok(?string $token): bool
    {
        $simpan = Setting::get(self::KUNCI_TOKEN);

        return $simpan && $token && hash_equals($simpan, hash('sha256', $token));
    }

    public static function dijeda(): bool
    {
        return Setting::get(self::KUNCI_JEDA) === '1';
    }

    public static function setJeda(bool $jeda): void
    {
        Setting::set(self::KUNCI_JEDA, $jeda ? '1' : '0');
    }

    /** @param  string|null  $masalah  mis. "Belum login di submitin.id" — NULL bila tidak ada */
    public static function catatDetak(?string $masalah = null): void
    {
        Setting::set(self::KUNCI_DETAK, now()->toIso8601String());
        Setting::set(self::KUNCI_MASALAH, $masalah ? Str::limit($masalah, 300) : '');
    }

    public static function detakTerakhir(): ?Carbon
    {
        $nilai = Setting::get(self::KUNCI_DETAK);

        return $nilai ? Carbon::parse($nilai) : null;
    }

    public static function botAktif(): bool
    {
        $detak = self::detakTerakhir();

        return $detak && $detak->gt(now()->subMinutes(self::AKTIF_MENIT));
    }

    public static function masalahBot(): ?string
    {
        return Setting::get(self::KUNCI_MASALAH) ?: null;
    }

    /** @return array{pesan: string, at: string}|null */
    public static function kuotaHabis(): ?array
    {
        $nilai = Setting::get(self::KUNCI_KUOTA);
        $data = $nilai ? json_decode($nilai, true) : null;

        return is_array($data) && ! empty($data['at']) ? $data : null;
    }

    public static function tandaiKuotaHabis(string $pesan): void
    {
        Setting::set(self::KUNCI_KUOTA, json_encode([
            'pesan' => Str::limit($pesan, 300),
            'at' => now()->toIso8601String(),
        ]));
    }

    /** Admin sudah mengisi ulang paket — bot boleh mengambil tugas lagi. */
    public static function kuotaSudahDiisi(): void
    {
        Setting::set(self::KUNCI_KUOTA, '');
    }

    /* ------------------------------------------------------------------
     | Antrean
     * ------------------------------------------------------------------ */

    /**
     * Tugas untuk bot: yang sedang berjalan (dilanjutkan), atau yang tertua
     * dalam antrean. NULL bila dijeda, kuota habis, atau antrean kosong.
     */
    public static function ambilTugas(): ?OrderUpload
    {
        if (self::dijeda() || self::kuotaHabis()) {
            return null;
        }

        return DB::transaction(function () {
            $berjalan = OrderUpload::whereIn('bot_status', [self::DIAMBIL, self::MENUNGGU_HASIL])
                ->where('status', 'diproses')
                ->orderBy('bot_diambil_at')
                ->lockForUpdate()
                ->first();

            if ($berjalan) {
                return $berjalan;
            }

            $calon = OrderUpload::query()
                ->where('jenis', 'plagiasi')
                ->where('status', 'menunggu')
                ->whereNull('bot_status')
                ->whereNotNull('path')
                ->whereHas('order', fn ($q) => $q->whereIn('status', ['paid', 'processing', 'completed']))
                ->orderBy('created_at')
                ->lockForUpdate()
                ->first();

            if (! $calon) {
                return null;
            }

            $calon->update([
                'status' => 'diproses',
                'diproses_at' => now(),
                'dikerjakan_oleh' => 'bot',
                'bot_status' => self::DIAMBIL,
                'bot_kode' => null,
                'bot_pesan' => null,
                'bot_diambil_at' => now(),
                'bot_diperbarui_at' => now(),
            ]);

            return $calon;
        });
    }

    /**
     * Nama berkas yang diunggah ke submitin.
     *
     * Nama asli customer TIDAK dipakai: sering memuat nama orangnya ("Skripsi
     * Budi Santoso.docx") dan akan tampil di riwayat akun submitin. Sebagai
     * gantinya penanda unik unggahan + nomor pesanan, yang juga dipakai skrip
     * untuk memastikan halaman status yang dibacanya milik unggahan ini.
     */
    public static function penanda(OrderUpload $up): string
    {
        return 'PD-'.strtoupper(substr(str_replace('-', '', $up->id), 0, 8));
    }

    public static function namaBerkas(OrderUpload $up): string
    {
        $ext = strtolower(pathinfo((string) $up->path, PATHINFO_EXTENSION)) ?: 'pdf';

        return self::penanda($up).'-'.$up->order->order_number.'.'.$ext;
    }

    /** Data yang dikirim ke skrip. Tidak memuat nama, HP, maupun email customer. */
    public static function dataTugas(OrderUpload $up): array
    {
        return [
            'id' => $up->id,
            'order_number' => $up->order->order_number,
            'penanda' => self::penanda($up),
            'nama_berkas' => self::namaBerkas($up),
            'judul' => self::penanda($up).' '.$up->order->order_number,
            'bot_status' => $up->bot_status,
            'bot_kode' => $up->bot_kode,
            'filter' => self::filter($up),
        ];
    }

    /**
     * Setelan customer diterjemahkan ke kolom form submitin.id
     * (excl_biblio, excl_quotes, excl_source + tipe/nilai, excl_match + kata).
     */
    public static function filter(OrderUpload $up): array
    {
        $sumber = null;
        if ($up->exclude_sumber_kecil && preg_match('/(\d+)\s*(%|kata)?/i', (string) $up->ambang_sumber_kecil, $m)) {
            $sumber = [
                'tipe' => (isset($m[2]) && strtolower($m[2]) === 'kata') ? 'words' : 'percent',
                'nilai' => max(1, (int) $m[1]),
            ];
        }

        return [
            'excl_biblio' => (bool) $up->exclude_bibliografi,
            'excl_quotes' => (bool) $up->exclude_kutipan,
            'excl_source' => $sumber,
            'excl_match' => $up->exclude_kecocokan_kecil && $up->ambang_kecocokan_kecil
                ? ['kata' => max(1, (int) $up->ambang_kecocokan_kecil)]
                : null,
        ];
    }

    /** Skrip melaporkan form sudah terkirim dan submitin memberi kode order. */
    public static function tandaiTerkirim(OrderUpload $up, string $kode): void
    {
        $kode = strtoupper(trim($kode));

        if (! preg_match('/^SC-[A-Z0-9]{6,32}$/', $kode)) {
            throw new BotTurnitinDitolak('Kode order submitin tidak dikenali: '.$kode);
        }

        DB::transaction(function () use ($up, $kode) {
            $up = OrderUpload::whereKey($up->id)->lockForUpdate()->firstOrFail();

            // Dikirim ulang dengan kode yang sama (mis. koneksi putus) — aman.
            if ($up->bot_status === self::MENUNGGU_HASIL && $up->bot_kode === $kode) {
                return;
            }

            if ($up->bot_status !== self::DIAMBIL || $up->status !== 'diproses') {
                throw new BotTurnitinDitolak('Unggahan ini tidak sedang menunggu dikirim oleh bot.');
            }

            // Satu kode submitin hanya boleh milik SATU unggahan.
            if (OrderUpload::where('bot_kode', $kode)->whereKeyNot($up->id)->exists()) {
                throw new BotTurnitinDitolak('Kode '.$kode.' sudah tercatat untuk unggahan lain.');
            }

            $up->update([
                'bot_status' => self::MENUNGGU_HASIL,
                'bot_kode' => $kode,
                'bot_diperbarui_at' => now(),
            ]);
        });
    }

    /** Bot masih menunggu hasil — supaya tidak dianggap macet. */
    public static function masihMenunggu(OrderUpload $up): void
    {
        if (in_array($up->bot_status, [self::DIAMBIL, self::MENUNGGU_HASIL], true)) {
            $up->update(['bot_diperbarui_at' => now()]);
        }
    }

    /**
     * Simpan laporan dari submitin untuk unggahan ini.
     *
     * @return string 'selesai' | 'perlu_dilengkapi'
     */
    public static function simpanHasil(OrderUpload $up, string $kode, UploadedFile $pdf, ?int $persenHalaman = null): string
    {
        $kode = strtoupper(trim($kode));

        $kepala = (string) @file_get_contents($pdf->getRealPath(), false, null, 0, 5);
        if ($kepala !== '%PDF-') {
            throw new BotTurnitinDitolak('Berkas yang dikirim bukan PDF.');
        }

        $hasil = DB::transaction(function () use ($up, $kode, $pdf, $persenHalaman) {
            $up = OrderUpload::whereKey($up->id)->lockForUpdate()->firstOrFail();

            if ($up->bot_status !== self::MENUNGGU_HASIL || $up->status !== 'diproses') {
                throw new BotTurnitinDitolak('Unggahan ini tidak sedang menunggu hasil dari bot (mungkin sudah dikerjakan admin).');
            }

            if (! $up->bot_kode || ! hash_equals($up->bot_kode, $kode)) {
                throw new BotTurnitinDitolak('Kode order submitin tidak cocok dengan unggahan ini. Hasil TIDAK disimpan.');
            }

            $order = $up->order()->with(['customer', 'items.product', 'uploads'])->firstOrFail();
            $folder = 'order-uploads/'.$order->id.'/hasil';

            if ($up->hasil_path && Storage::disk('local')->exists($up->hasil_path)) {
                Storage::disk('local')->delete($up->hasil_path);
            }

            $path = $pdf->store($folder, 'local');
            $persen = PlagiarismReader::persenDariPdf(Storage::disk('local')->path($path)) ?? $persenHalaman;

            $up->fill([
                'hasil_path' => $path,
                'hasil_nama' => 'Hasil Turnitin '.$order->order_number.'.pdf',
                'hasil_ukuran' => $pdf->getSize(),
                'hasil_mime' => 'application/pdf',
                'persentase' => is_null($persen) ? null : max(0, min(100, (int) $persen)),
                'dikerjakan_oleh' => 'bot',
                'bot_diperbarui_at' => now(),
            ]);

            // Pesanan yang juga menuntut hasil lain pada unggahan ini (mis.
            // add-on Cek AI) TIDAK ditandai selesai: pelanggan jangan menerima
            // separuh hasil. Admin melengkapinya dari kartu dashboard.
            $kurang = HasilPengecekan::yangKurang($order, $up);

            if ($kurang) {
                $up->bot_status = self::PERLU_DILENGKAPI;
                $up->bot_pesan = 'Hasil plagiasi sudah diunggah bot. Masih kurang: '.implode(', ', $kurang).'.';
                $up->save();

                return [self::PERLU_DILENGKAPI, $order, $up];
            }

            $up->status = 'selesai';
            $up->selesai_at = now();
            $up->bot_status = self::SELESAI;
            $up->bot_pesan = null;
            $up->save();

            return [self::SELESAI, $order, $up];
        });

        [$status, $order, $up] = $hasil;

        if ($status === self::SELESAI) {
            self::kirimEmailHasil($order, $up);

            $order->load('uploads');
            if ($order->jasaTuntas()) {
                app(SelesaikanPesananJasa::class)->execute($order);
            }
        }

        return $status;
    }

    /**
     * Bot gagal mengerjakan. Unggahan yang BELUM terkirim ke submitin
     * dikembalikan ke antrean admin; yang sudah terkirim tetap 'diproses'
     * (laporannya mungkin masih keluar di submitin — kodenya disimpan).
     */
    public static function gagal(OrderUpload $up, string $pesan, bool $kuotaHabis = false): void
    {
        DB::transaction(function () use ($up, $pesan, $kuotaHabis) {
            $up = OrderUpload::whereKey($up->id)->lockForUpdate()->firstOrFail();

            if (! in_array($up->bot_status, [self::DIAMBIL, self::MENUNGGU_HASIL], true)) {
                return;
            }

            $belumTerkirim = $up->bot_status === self::DIAMBIL;

            if ($kuotaHabis && $belumTerkirim) {
                // Bukan salah unggahannya: kembalikan ke antrean apa adanya,
                // bot mengambilnya lagi begitu admin mengisi ulang paket.
                $up->update([
                    'status' => 'menunggu',
                    'diproses_at' => null,
                    'dikerjakan_oleh' => null,
                    'bot_status' => null,
                    'bot_pesan' => null,
                    'bot_diambil_at' => null,
                    'bot_diperbarui_at' => null,
                ]);

                return;
            }

            $up->update(array_merge([
                'bot_status' => self::GAGAL,
                'bot_pesan' => Str::limit($pesan, 480),
                'bot_diperbarui_at' => now(),
            ], $belumTerkirim ? [
                'status' => 'menunggu',
                'diproses_at' => null,
                'dikerjakan_oleh' => null,
            ] : []));
        });

        if ($kuotaHabis) {
            self::tandaiKuotaHabis($pesan);
        }
    }

    /* ------------------------------------------------------------------
     | Dashboard admin
     * ------------------------------------------------------------------ */

    /** Unggahan yang perlu tangan admin: gagal, perlu dilengkapi, atau macet. */
    public static function perluAdmin(): Collection
    {
        $batasMacet = now()->subMinutes(self::MACET_MENIT);

        return OrderUpload::with('order')
            ->whereIn('status', ['menunggu', 'diproses'])
            ->where(function ($q) use ($batasMacet) {
                $q->whereIn('bot_status', [self::GAGAL, self::PERLU_DILENGKAPI])
                    ->orWhere(fn ($q) => $q->whereIn('bot_status', [self::DIAMBIL, self::MENUNGGU_HASIL])
                        ->where('bot_diperbarui_at', '<', $batasMacet));
            })
            ->orderBy('bot_diperbarui_at')
            ->get();
    }

    public static function macet(OrderUpload $up): bool
    {
        return in_array($up->bot_status, [self::DIAMBIL, self::MENUNGGU_HASIL], true)
            && $up->bot_diperbarui_at
            && $up->bot_diperbarui_at->lt(now()->subMinutes(self::MACET_MENIT));
    }

    /** Admin mengambil alih: bot tidak akan menyentuh unggahan ini lagi. */
    public static function ambilAlih(OrderUpload $up): void
    {
        $belumTerkirim = $up->bot_status === self::DIAMBIL || ($up->bot_status === self::GAGAL && ! $up->bot_kode);

        $up->update(array_merge(
            ['bot_status' => self::MANUAL, 'bot_diperbarui_at' => now()],
            $belumTerkirim && $up->status === 'diproses'
                ? ['status' => 'menunggu', 'diproses_at' => null, 'dikerjakan_oleh' => null]
                : []
        ));
    }

    /**
     * Serahkan lagi ke bot. Hanya untuk unggahan yang BELUM pernah terkirim ke
     * submitin — yang sudah punya kode bisa jadi sudah memakai kuota, dan
     * mengirim ulang berarti membayar dua kali.
     */
    public static function cobaLagi(OrderUpload $up): bool
    {
        if ($up->bot_kode || ! in_array($up->status, ['menunggu', 'diproses'], true)) {
            return false;
        }

        $up->update([
            'status' => 'menunggu',
            'diproses_at' => null,
            'dikerjakan_oleh' => null,
            'bot_status' => null,
            'bot_pesan' => null,
            'bot_diambil_at' => null,
            'bot_diperbarui_at' => null,
        ]);

        return true;
    }

    /**
     * Sama dengan OrderDetail::kirimEmailHasil — gagal kirim email tidak boleh
     * membatalkan hasil yang sudah tersimpan. Mailer 'phoenix' disetel di
     * JasaHasilMail sendiri.
     */
    private static function kirimEmailHasil($order, OrderUpload $up): void
    {
        try {
            $email = optional($order->customer)->email;
            if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return;
            }

            Mail::mailer('phoenix')->to($email)->send(new JasaHasilMail($order, $up));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
