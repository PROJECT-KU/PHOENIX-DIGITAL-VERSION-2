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

        $nyangkut = self::hitungOrderNyangkut();
        $baris[] = $nyangkut === 0
            ? '✅ Tidak ada order nyangkut'
            : "⚠️ {$nyangkut} order pending lewat batas waktu";

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
     * Order pending yang sudah lewat batas bayar.
     *
     * Hanya nomor order + metode + umur. TIDAK ada nama/kontak customer.
     */
    private static function orderNyangkut(): string
    {
        $order = Order::where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes(30))
            ->orderByDesc('created_at')
            ->limit(15)
            ->get(['order_number', 'payment_method', 'created_at', 'expired_at']);

        if ($order->isEmpty()) {
            return '✅ Tidak ada order pending yang lewat batas waktu.';
        }

        $total = self::hitungOrderNyangkut();

        $baris = ["⚠️ ORDER NYANGKUT ({$total})", str_repeat('─', 24), ''];

        foreach ($order as $o) {
            $baris[] = '• '.$o->order_number
                .' — '.($o->payment_method ?: 'tanpa metode')
                // locale('id') dipaksa: APP_LOCALE=en (lihat CLAUDE.md).
                .' — '.$o->created_at->locale('id')->diffForHumans();
        }

        if ($total > $order->count()) {
            $baris[] = '';
            $baris[] = '… dan '.($total - $order->count()).' lainnya.';
        }

        $baris[] = '';
        $baris[] = 'Langkah: /qris untuk cek yang sudah dibayar, '
            .'lalu /order_batalkan untuk membatalkan sisanya.';

        return implode("\n", $baris);
    }

    private static function antrian(): string
    {
        return "📦 ANTRIAN\n".str_repeat('─', 24)."\n\n".self::barisAntrian();
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

    private static function hitungOrderNyangkut(): int
    {
        return Order::where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes(30))
            ->count();
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
