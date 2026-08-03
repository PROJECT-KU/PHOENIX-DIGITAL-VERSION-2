<?php

namespace App\Services\Telegram;

use App\Models\ActivityLog;
use App\Models\Order;
use App\Support\CronHeartbeat;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DAFTAR PUTIH aksi yang boleh dijalankan bot Telegram.
 *
 * Ini satu-satunya pintu eksekusi bot. Tidak ada shell, tidak ada artisan
 * bebas, tidak ada eval. Menambah kemampuan bot = menambah entri di sini,
 * secara sadar. Kalau token bot bocor, kerusakan maksimal terbatas pada
 * daftar ini.
 *
 * ATURAN ISI BALASAN (wajib):
 * Telegram adalah layanan LUAR. Balasan TIDAK BOLEH memuat data customer —
 * nama, no_hp, email, isi pesan, username/password akun. Yang boleh keluar
 * hanya angka agregat, status, nomor order (identifier internal), dan pesan
 * error teknis. Lihat CLAUDE.md / skill phoenix-digital.
 */
class AksiAgen
{
    /**
     * Definisi seluruh aksi.
     *
     * @return array<string,array{judul:string,jelas:string,ubah:bool}>
     */
    public static function daftar(): array
    {
        return [
            'status' => [
                'judul' => 'Status sistem',
                'jelas' => 'Ringkasan kesehatan: cron, order QRIS nyangkut, antrian, database, disk.',
                'ubah' => false,
            ],
            'cron' => [
                'judul' => 'Cek cron',
                'jelas' => 'Periksa apakah cron server masih menyala dan kapan terakhir jalan.',
                'ubah' => false,
            ],
            'cron_jalankan' => [
                'judul' => 'Paksa jalankan cron',
                'jelas' => 'Jalankan sekarang: cek pembayaran QRIS, batalkan order kedaluwarsa, lalu scheduler penuh. Dipakai saat cron mati.',
                'ubah' => true,
            ],
            'qris' => [
                'judul' => 'Cek pembayaran QRIS',
                'jelas' => 'Tanyakan ke penyedia QRIS apakah order pending sudah dibayar, tandai lunas bila sudah.',
                'ubah' => true,
            ],
            'order_nyangkut' => [
                'judul' => 'Order nyangkut',
                'jelas' => 'Daftar order yang masih pending melewati batas waktu bayar. Hanya melihat, tidak mengubah.',
                'ubah' => false,
            ],
            'order_batalkan' => [
                'judul' => 'Batalkan order kedaluwarsa',
                'jelas' => 'Jalankan pembatalan otomatis order yang sudah lewat batas waktu pembayaran.',
                'ubah' => true,
            ],
            'trafik' => [
                'judul' => 'Trafik pengunjung',
                'jelas' => 'Jumlah kunjungan & pengunjung unik hari ini, perbandingan dengan kemarin, dan halaman terpopuler.',
                'ubah' => false,
            ],
            'antrian' => [
                'judul' => 'Antrian & job gagal',
                'jelas' => 'Jumlah job menunggu dan job gagal di antrian.',
                'ubah' => false,
            ],
            'log' => [
                'judul' => 'Ringkasan error',
                'jelas' => 'Error terbanyak 24 jam terakhir dari Log Aktivitas.',
                'ubah' => false,
            ],
            'cache_bersih' => [
                'judul' => 'Bersihkan cache',
                'jelas' => 'Jalankan optimize:clear (config, route, view, cache). Dipakai setelah deploy atau saat tampilan/konfigurasi tidak berubah.',
                'ubah' => true,
            ],
            'bantuan' => [
                'judul' => 'Bantuan',
                'jelas' => 'Tampilkan daftar perintah yang tersedia.',
                'ubah' => false,
            ],
        ];
    }

    /** Kunci aksi yang valid. @return array<int,string> */
    public static function kunci(): array
    {
        return array_keys(self::daftar());
    }

    public static function ada(string $aksi): bool
    {
        return array_key_exists($aksi, self::daftar());
    }

    /**
     * Menu perintah untuk Telegram (setMyCommands) — yang muncul saat pengguna
     * mengetik "/".
     *
     * Memakai `judul` (pendek) sebagai keterangan, bukan `jelas`, supaya
     * menunya enak dibaca di layar HP. Aksi yang mengubah keadaan ditandai ✏️.
     *
     * Batas Telegram: perintah 1–32 karakter (huruf kecil, angka, garis bawah)
     * dan keterangan 1–256 karakter — seluruh kunci di daftar() sudah memenuhi.
     *
     * @return array<int,array{command:string,description:string}>
     */
    public static function menuTelegram(): array
    {
        $menu = [];

        foreach (self::daftar() as $kunci => $info) {
            $menu[] = [
                'command' => $kunci,
                'description' => $info['judul'].($info['ubah'] ? ' ✏️' : ''),
            ];
        }

        return $menu;
    }

    /**
     * Jalankan satu aksi dari daftar putih.
     *
     * Aksi tak dikenal TIDAK dieksekusi — dikembalikan sebagai pesan bantuan.
     */
    public static function jalankan(string $aksi): string
    {
        if (! self::ada($aksi)) {
            return "Aksi tidak dikenal: {$aksi}\n\n".self::bantuan();
        }

        try {
            return match ($aksi) {
                'status' => self::status(),
                'cron' => self::cron(),
                'cron_jalankan' => self::cronJalankan(),
                'qris' => self::qris(),
                'order_nyangkut' => self::orderNyangkut(),
                'order_batalkan' => self::orderBatalkan(),
                'trafik' => self::trafik(),
                'antrian' => self::antrian(),
                'log' => self::log(),
                'cache_bersih' => self::cacheBersih(),
                'bantuan' => self::bantuan(),
            };
        } catch (\Throwable $e) {
            report($e);

            return "⚠️ Aksi '{$aksi}' gagal dijalankan.\n\n"
                .get_class($e).': '.$e->getMessage()
                ."\n".basename($e->getFile()).':'.$e->getLine();
        }
    }

    // ---------------------------------------------------------------- aksi --

    private static function status(): string
    {
        $baris = ['📊 STATUS SISTEM', str_repeat('─', 24), ''];

        $baris[] = CronHeartbeat::ringkasan();
        $baris[] = '';

        [$otomatis, $manual] = self::hitungOrderMenggantung();
        $baris[] = ($otomatis + $manual) === 0
            ? '✅ Tidak ada order menggantung'
            : "⚠️ Order menggantung: {$otomatis} perlu dibatalkan, {$manual} menunggu konfirmasi (/order_nyangkut)";

        $baris[] = self::barisAntrian();
        $baris[] = self::barisDatabase();
        $baris[] = self::barisDisk();

        $error = self::hitungError24Jam();
        $baris[] = $error === 0
            ? '✅ Tidak ada error 24 jam terakhir'
            : "⚠️ {$error} error dalam 24 jam terakhir (/log)";

        $baris[] = '';
        $baris[] = 'Waktu server: '.now()->format('d/m/Y H:i:s');

        return implode("\n", $baris);
    }

    private static function cron(): string
    {
        return "⏰ CRON\n".str_repeat('─', 24)."\n\n"
            .CronHeartbeat::ringkasan()
            ."\n\nKalau cron server mati, /cron_jalankan memaksa tugas berjalan sekarang.";
    }

    /**
     * Jalankan tugas terjadwal sekarang juga.
     *
     * Urutannya SAMA dengan CronController: dua tugas kritis in-process dulu
     * (tanpa mutex withoutOverlapping yang bisa nyangkut), baru scheduler penuh.
     */
    private static function cronJalankan(): string
    {
        $baris = ['🔧 MENJALANKAN TUGAS TERJADWAL', str_repeat('─', 24), ''];

        foreach (['qris:cek-pembayaran', 'orders:cancel-expired'] as $perintah) {
            $baris[] = self::artisan($perintah);
        }

        $baris[] = self::artisan('schedule:run');

        return implode("\n", $baris);
    }

    private static function qris(): string
    {
        return "💳 CEK PEMBAYARAN QRIS\n".str_repeat('─', 24)."\n\n"
            .self::artisan('qris:cek-pembayaran');
    }

    private static function orderBatalkan(): string
    {
        return "🗑️ BATALKAN ORDER KEDALUWARSA\n".str_repeat('─', 24)."\n\n"
            .self::artisan('orders:cancel-expired');
    }

    /**
     * Order pending/draft, DIPISAH menurut apakah pembatal otomatis akan
     * menyentuhnya.
     *
     * Pemisahan ini penting: order `transfer`/`qris_statis` tanpa catatan
     * pembayaran dan tanpa expired_at TIDAK PERNAH dibatalkan otomatis — ia
     * menunggu konfirmasi admin. Versi pertama menyamaratakan semuanya sebagai
     * "nyangkut" lalu menyarankan /order_batalkan, yang tidak berbuat apa pun
     * untuk order jenis itu.
     *
     * Hanya nomor order + metode + umur. TIDAK ada nama/kontak customer.
     */
    private static function orderNyangkut(): string
    {
        $orders = Order::whereIn('status', ['pending', 'draft'])
            ->whereDoesntHave('payments', fn ($q) => $q->where('status', 'settlement'))
            ->with(['payments' => fn ($q) => $q->latest()])
            ->orderByDesc('created_at')
            ->limit(60)
            ->get();

        if ($orders->isEmpty()) {
            return '✅ Tidak ada order pending/draft yang menggantung.';
        }

        [$otomatis, $manual] = $orders->partition(fn ($o) => self::akanDibatalkanOtomatis($o));

        $baris = ['📋 ORDER MENGGANTUNG', str_repeat('─', 24)];

        $baris[] = '';
        $baris[] = 'AKAN DIBATALKAN OTOMATIS ('.$otomatis->count().')';

        if ($otomatis->isEmpty()) {
            $baris[] = '• tidak ada';
        } else {
            foreach ($otomatis->take(10) as $o) {
                $baris[] = '• '.self::barisOrder($o);
            }
            if ($otomatis->count() > 10) {
                $baris[] = '… dan '.($otomatis->count() - 10).' lainnya.';
            }
            $baris[] = '';
            $baris[] = '→ /qris dulu (siapa tahu sudah dibayar), lalu /order_batalkan.';
        }

        $baris[] = '';
        $baris[] = 'MENUNGGU KONFIRMASI ANDA ('.$manual->count().')';

        if ($manual->isEmpty()) {
            $baris[] = '• tidak ada';
        } else {
            foreach ($manual->take(10) as $o) {
                $baris[] = '• '.self::barisOrder($o);
            }
            if ($manual->count() > 10) {
                $baris[] = '… dan '.($manual->count() - 10).' lainnya.';
            }
            $baris[] = '';
            $baris[] = '→ Pembayaran manual (transfer/QRIS statis) tanpa batas waktu. '
                .'/order_batalkan TIDAK menyentuhnya — perlu dicek di admin.';
        }

        return implode("\n", $baris);
    }

    private static function barisOrder(Order $o): string
    {
        return $o->order_number
            .' — '.($o->payment_method ?: 'tanpa metode')
            .' ['.$o->status.']'
            // locale('id') dipaksa: APP_LOCALE=en (lihat CLAUDE.md).
            .' — '.$o->created_at?->locale('id')->diffForHumans();
    }

    /**
     * CERMINAN App\Console\Commands\CancelExpiredOrders.
     *
     * Sengaja meniru logikanya, BUKAN menebak, supaya laporan bot tidak
     * menjanjikan pembatalan yang tak akan terjadi. Kalau kriteria di command
     * itu berubah, ubah juga di sini.
     */
    private static function akanDibatalkanOtomatis(Order $o): bool
    {
        if ($o->status === 'draft') {
            return $o->created_at !== null
                && $o->created_at->lessThanOrEqualTo(now()->subHours(24));
        }

        $terakhir = $o->payments->first();

        if ($terakhir && ($terakhir->status === 'expire'
            || ($terakhir->expired_at && $terakhir->expired_at->lessThanOrEqualTo(now())))) {
            return true;
        }

        return $o->expired_at !== null && $o->expired_at->lessThanOrEqualTo(now());
    }

    private static function antrian(): string
    {
        return "📦 ANTRIAN\n".str_repeat('─', 24)."\n\n".self::barisAntrian();
    }

    /**
     * Trafik pengunjung dari Log Aktivitas.
     *
     * Sumbernya TrackRequestPerformance, yang hanya mencatat page view asli
     * (GET + HTML + sukses) — aset, Livewire, dan endpoint internal dilewati,
     * jadi angkanya bukan hitungan request mentah.
     *
     * Privasi: yang keluar hanya ANGKA agregat + URL halaman sendiri. Alamat IP
     * dipakai untuk menghitung pengunjung unik tapi TIDAK PERNAH ditampilkan.
     */
    private static function trafik(): string
    {
        if (! Schema::hasTable('activity_logs')) {
            return 'Tabel activity_logs belum ada.';
        }

        $kunjungan = fn ($dari, $sampai) => ActivityLog::where('type', 'visit')
            ->whereBetween('created_at', [$dari, $sampai]);

        $awalHariIni = today();
        $sekarang = now();
        $awalKemarin = today()->subDay();

        $hariIni = (clone $kunjungan($awalHariIni, $sekarang))->count();
        $unikHariIni = (clone $kunjungan($awalHariIni, $sekarang))->distinct('ip')->count('ip');

        // Dibandingkan pada JAM YANG SAMA, bukan sehari penuh — kalau tidak,
        // pagi hari akan selalu terlihat "turun drastis" dari kemarin.
        $kemarinSejam = (clone $kunjungan($awalKemarin, $awalKemarin->copy()->addSeconds($awalHariIni->diffInSeconds($sekarang))))->count();

        $baris = ['📈 TRAFIK PENGUNJUNG', str_repeat('─', 24), ''];

        $baris[] = 'HARI INI (s/d '.$sekarang->format('H:i').')';
        $baris[] = '• Kunjungan halaman : '.number_format($hariIni, 0, ',', '.');
        $baris[] = '• Pengunjung unik   : '.number_format($unikHariIni, 0, ',', '.');
        $baris[] = '• Sedang aktif (5 mnt): '
            .(clone $kunjungan($sekarang->copy()->subMinutes(5), $sekarang))->distinct('ip')->count('ip');

        $baris[] = '';
        $baris[] = 'vs KEMARIN (jam yang sama): '.self::selisih($hariIni, $kemarinSejam);

        // 6 jam terakhir — 6 query murah, sengaja tidak memakai fungsi tanggal
        // khusus MySQL supaya tetap jalan di SQLite (dipakai phpunit).
        $baris[] = '';
        $baris[] = '6 JAM TERAKHIR';

        $perJam = [];
        for ($i = 5; $i >= 0; $i--) {
            $mulai = $sekarang->copy()->subHours($i)->startOfHour();
            $perJam[$mulai->format('H')] = (clone $kunjungan($mulai, $mulai->copy()->endOfHour()))->count();
        }

        $puncak = max(1, max($perJam));
        foreach ($perJam as $jam => $jml) {
            $bar = str_repeat('█', (int) round($jml / $puncak * 12));
            $baris[] = sprintf('%s:00 %-12s %d', $jam, $bar, $jml);
        }

        $populer = (clone $kunjungan($awalHariIni, $sekarang))
            ->select('url', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('url')
            ->orderByDesc('jumlah')
            ->limit(5)
            ->get();

        if ($populer->isNotEmpty()) {
            $baris[] = '';
            $baris[] = 'HALAMAN TERPOPULER HARI INI';

            foreach ($populer as $p) {
                $jalur = parse_url((string) $p->url, PHP_URL_PATH) ?: '/';
                $jalur = mb_strlen($jalur) > 40 ? mb_substr($jalur, 0, 40).'…' : $jalur;
                $baris[] = '• '.$p->jumlah.'×  '.$jalur;
            }
        }

        $baris[] = '';
        $baris[] = 'ℹ️ Riwayat kunjungan disimpan 7 hari (activity-logs:prune).';

        return implode("\n", $baris);
    }

    /** "+12% (naik 34)" / "-8% (turun 12)" / "sama". */
    private static function selisih(int $sekarang, int $sebelum): string
    {
        if ($sebelum === 0) {
            return $sekarang === 0 ? 'sama (0)' : "+{$sekarang} (kemarin belum ada data)";
        }

        $delta = $sekarang - $sebelum;

        if ($delta === 0) {
            return 'sama ('.$sebelum.')';
        }

        $persen = (int) round($delta / $sebelum * 100);

        return ($delta > 0 ? '📈 +' : '📉 ').$persen.'% ('
            .($delta > 0 ? 'naik ' : 'turun ').abs($delta).', kemarin '.$sebelum.')';
    }

    /**
     * Error terbanyak 24 jam terakhir.
     *
     * Dikelompokkan per pesan supaya ringkas. Pesan error teknis boleh keluar;
     * kolom identitas (user_name, ip) sengaja TIDAK diambil.
     */
    private static function log(): string
    {
        if (! Schema::hasTable('activity_logs')) {
            return 'Tabel activity_logs belum ada.';
        }

        $error = ActivityLog::where('type', 'error')
            ->where('created_at', '>=', now()->subDay())
            ->select('message', 'exception_class', DB::raw('COUNT(*) as jumlah'), DB::raw('MAX(created_at) as terakhir'))
            ->groupBy('message', 'exception_class')
            ->orderByDesc('jumlah')
            ->limit(10)
            ->get();

        if ($error->isEmpty()) {
            return '✅ Tidak ada error dalam 24 jam terakhir.';
        }

        $baris = ['🐞 ERROR 24 JAM TERAKHIR', str_repeat('─', 24), ''];

        foreach ($error as $e) {
            $pesan = trim((string) $e->message);
            $pesan = mb_strlen($pesan) > 160 ? mb_substr($pesan, 0, 160).'…' : $pesan;

            $baris[] = '['.$e->jumlah.'×] '.class_basename((string) $e->exception_class);
            $baris[] = '   '.($pesan !== '' ? $pesan : '(tanpa pesan)');
            $baris[] = '';
        }

        return rtrim(implode("\n", $baris));
    }

    private static function cacheBersih(): string
    {
        return "🧹 BERSIHKAN CACHE\n".str_repeat('─', 24)."\n\n"
            .self::artisan('optimize:clear');
    }

    public static function bantuan(): string
    {
        $baris = ['🤖 AGEN PHOENIX DIGITAL', str_repeat('─', 24), ''];

        foreach (self::daftar() as $kunci => $info) {
            $baris[] = '/'.$kunci.($info['ubah'] ? ' ✏️' : '');
            $baris[] = '   '.$info['jelas'];
            $baris[] = '';
        }

        $baris[] = '✏️ = mengubah keadaan sistem.';

        if (AgenAi::aktif()) {
            $baris[] = '';
            $baris[] = 'Mode AI aktif — Anda juga bisa mengetik bebas, '
                .'misalnya "cron mati ya? tolong cek".';
        }

        return rtrim(implode("\n", $baris));
    }

    // -------------------------------------------------------------- bantu ---

    /** Jalankan satu perintah artisan dari daftar putih & rangkum keluarannya. */
    private static function artisan(string $perintah): string
    {
        try {
            Artisan::call($perintah);
            $keluaran = trim(Artisan::output());

            if ($keluaran === '') {
                return '✅ '.$perintah.' — selesai';
            }

            if (mb_strlen($keluaran) > 600) {
                $keluaran = mb_substr($keluaran, 0, 600).'…';
            }

            return '✅ '.$perintah."\n".$keluaran;
        } catch (\Throwable $e) {
            report($e);

            return '❌ '.$perintah.' GAGAL: '.$e->getMessage();
        }
    }

    /**
     * @return array{0:int,1:int} [akan dibatalkan otomatis, menunggu konfirmasi]
     */
    private static function hitungOrderMenggantung(): array
    {
        $orders = Order::whereIn('status', ['pending', 'draft'])
            ->whereDoesntHave('payments', fn ($q) => $q->where('status', 'settlement'))
            ->with(['payments' => fn ($q) => $q->latest()])
            ->get();

        $otomatis = $orders->filter(fn ($o) => self::akanDibatalkanOtomatis($o))->count();

        return [$otomatis, $orders->count() - $otomatis];
    }

    private static function hitungError24Jam(): int
    {
        if (! Schema::hasTable('activity_logs')) {
            return 0;
        }

        return ActivityLog::where('type', 'error')
            ->where('created_at', '>=', now()->subDay())
            ->count();
    }

    private static function barisAntrian(): string
    {
        try {
            $menunggu = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;
            $gagal = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;
        } catch (\Throwable $e) {
            return '⚠️ Antrian: tidak terbaca ('.$e->getMessage().')';
        }

        if ($gagal > 0) {
            return "⚠️ Antrian: {$menunggu} menunggu, {$gagal} GAGAL";
        }

        return "✅ Antrian: {$menunggu} menunggu, 0 gagal";
    }

    private static function barisDatabase(): string
    {
        try {
            DB::select('select 1');

            return '✅ Database terhubung';
        } catch (\Throwable $e) {
            return '🔴 Database GAGAL: '.$e->getMessage();
        }
    }

    private static function barisDisk(): string
    {
        try {
            $bebas = @disk_free_space(base_path());
            $total = @disk_total_space(base_path());

            if (! $bebas || ! $total) {
                return 'ℹ️ Disk: tidak terbaca di hosting ini';
            }

            $persenTerpakai = (int) round(($total - $bebas) / $total * 100);
            $gbBebas = round($bebas / 1024 ** 3, 1);

            return ($persenTerpakai >= 90 ? '⚠️' : '✅')
                ." Disk: {$persenTerpakai}% terpakai, sisa {$gbBebas} GB";
        } catch (\Throwable $e) {
            return 'ℹ️ Disk: tidak terbaca';
        }
    }
}
