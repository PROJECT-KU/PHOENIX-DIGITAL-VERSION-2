<?php

namespace App\Support\Surat;

use App\Models\Surat\Surat;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Satu tempat untuk tab, saringan, dan urutan buku agenda.
 *
 * SENGAJA tidak tinggal di dalam SuratList.
 *
 * Layar daftar dan tombol ekspor harus memulangkan kumpulan surat yang SAMA
 * PERSIS. Begitu logikanya disalin, ekspor diam-diam melepaskan satu saringan
 * — dan tidak ada yang menyadarinya, karena berkasnya tetap terunduh, tetap
 * berisi surat sungguhan, hanya saja lebih banyak daripada yang terlihat di
 * layar. Laporan yang memuat surat yang sengaja disaring keluar jauh lebih
 * berbahaya daripada tombol yang gagal.
 *
 * Penyaring kerahasiaan (terlihatOleh) ikut di sini, bukan di pemanggilnya,
 * supaya surat rahasia tidak bisa keluar lewat pintu ekspor.
 */
class SaringanSurat
{
    /** Nama saringan di alamat halaman — sama dengan #[Url] di SuratList. */
    public const KUNCI = ['tab', 'cari', 'klasifikasi', 'sifat', 'status', 'dari', 'sampai', 'urut'];

    public const URUTAN = [
        'terbaru' => 'Tanggal surat terbaru di atas',
        'terlama' => 'Tanggal surat terlama di atas',
        'agenda' => 'Nomor agenda terbesar di atas',
        'dicatat' => 'Terakhir dicatat di atas',
    ];

    /** Ambil saringan dari alamat halaman, apa adanya. */
    public static function dariPermintaan(Request $r): array
    {
        $hasil = [];

        foreach (self::KUNCI as $kunci) {
            $hasil[$kunci] = trim((string) $r->query($kunci, ''));
        }

        return $hasil;
    }

    /** Kueri dasar: kerahasiaan, saringan, lalu tab. */
    public static function kueri(?User $pengguna, array $saringan): Builder
    {
        $q = Surat::query()
            ->terlihatOleh($pengguna)
            ->cari($saringan['cari'] ?? null)
            ->klasifikasi(($saringan['klasifikasi'] ?? '') ?: null)
            ->sifat(($saringan['sifat'] ?? '') ?: null)
            ->status(($saringan['status'] ?? '') ?: null)
            ->periode(($saringan['dari'] ?? '') ?: null, ($saringan['sampai'] ?? '') ?: null);

        return self::tab($q, $saringan['tab'] ?? 'semua');
    }

    public static function tab(Builder $q, ?string $tab): Builder
    {
        if ($tab === 'terhapus') {
            return $q->onlyTrashed();
        }

        return match ($tab) {
            'masuk' => $q->where('arah', 'masuk'),
            'keluar' => $q->where('arah', 'keluar'),
            'baru' => $q->where('status', 'baru'),
            'telat' => $q->whereHas('disposisi', fn (Builder $d) => $d->belumSelesai()
                ->whereNotNull('batas')
                ->whereDate('batas', '<', now()->toDateString())),
            'retensi' => $q->whereNotNull('musnah_pada')
                ->whereDate('musnah_pada', '<=', now()->toDateString())
                ->whereNotIn('status', ['diarsipkan', 'dimusnahkan']),
            default => $q,
        };
    }

    /**
     * Urutan SELALU dengan pemecah seri (id).
     *
     * tanggal_surat hanya tanggal, tanpa jam: puluhan surat bisa bertanggal
     * sama, dan urutan baris yang nilainya seri tidak dijamin. Tanpa pemecah,
     * satu surat bisa terbit di dua halaman sekaligus sementara surat lain
     * tidak terbit di halaman mana pun.
     */
    public static function urut(Builder $q, ?string $urut): Builder
    {
        return match ($urut) {
            'terlama' => $q->orderBy('tanggal_surat')->orderBy('id'),
            'agenda' => $q->orderByDesc('nomor_agenda')->orderByDesc('id'),
            'dicatat' => $q->orderByDesc('created_at')->orderByDesc('id'),
            default => $q->orderByDesc('tanggal_surat')->orderByDesc('id'),
        };
    }

    /** Kalimat "disaring apa saja", untuk kepala berkas ekspor. */
    public static function ringkasan(array $saringan, $daftarKlasifikasi = null): string
    {
        $bagian = [];

        $tab = $saringan['tab'] ?? 'semua';
        if ($tab !== '' && $tab !== 'semua') {
            $bagian[] = 'kelompok: '.($tab === 'terhapus' ? 'terhapus' : str_replace('_', ' ', $tab));
        }

        if (($saringan['cari'] ?? '') !== '') {
            $bagian[] = 'kata "'.$saringan['cari'].'"';
        }

        if (($saringan['klasifikasi'] ?? '') !== '' && $daftarKlasifikasi) {
            $k = $daftarKlasifikasi->firstWhere('id', (int) $saringan['klasifikasi']);
            $bagian[] = 'klasifikasi '.($k?->label ?? $saringan['klasifikasi']);
        }

        if (($saringan['sifat'] ?? '') !== '') {
            $bagian[] = 'sifat '.str_replace('_', ' ', $saringan['sifat']);
        }

        if (($saringan['status'] ?? '') !== '') {
            $bagian[] = 'status '.(Surat::STATUS[$saringan['status']] ?? $saringan['status']);
        }

        $dari = $saringan['dari'] ?? '';
        $sampai = $saringan['sampai'] ?? '';

        if ($dari !== '' || $sampai !== '') {
            $bagian[] = 'tanggal surat '.($dari !== '' ? $dari : '…').' s.d. '.($sampai !== '' ? $sampai : '…');
        }

        return $bagian === [] ? 'Seluruh arsip, tanpa saringan' : 'Disaring: '.implode(' · ', $bagian);
    }
}
