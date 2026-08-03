<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramClient;
use App\Support\CronHeartbeat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Kirim peringatan Telegram saat keandalan pemicu terjadwal BERUBAH.
 *
 * Kenapa ini, bukan tombol "hidupkan cron"?
 * Cron hPanel diatur di panel Hostinger dan `crontab` CLI diblokir di hosting
 * ini — aplikasi TIDAK PUNYA cara menyalakannya kembali. cron-job.org pun
 * layanan luar dengan akun sendiri. Yang benar-benar menolong bukan tombol
 * restart yang mustahil, melainkan tahu SEGERA saat ada yang mati, lalu
 * /cron_jalankan sebagai pertolongan pertama.
 *
 * BATAS YANG HARUS DISADARI: pemantau ini sendiri berjalan lewat scheduler.
 * Kalau SEMUA pemicu mati berbarengan, tidak ada yang menjalankannya, jadi
 * tidak ada peringatan yang terkirim. Ia menutup kasus yang paling sering
 * terjadi — satu pemicu mati sementara yang lain masih hidup — bukan kasus
 * mati total. Untuk mati total, cron-job.org punya notifikasi kegagalan
 * sendiri; nyalakan itu sebagai lapis terakhir.
 *
 * Hanya mengirim saat keadaan BERUBAH, jadi tidak membanjiri chat.
 */
class PantauCron extends Command
{
    protected $signature = 'cron:pantau {--paksa : Kirim laporan walau keadaan tidak berubah}';

    protected $description = 'Peringatkan lewat Telegram bila pemicu terjadwal mati atau pulih.';

    private const KUNCI = 'cron:keadaan-dilaporkan';

    public function handle(TelegramClient $telegram): int
    {
        $chatIds = config('telegram.chat_ids', []);

        if (! $telegram->aktif() || empty($chatIds)) {
            return self::SUCCESS;
        }

        $keadaan = CronHeartbeat::keadaan();

        // 'baru' = belum ada denyut sama sekali (baru ter-deploy). Bukan
        // kegagalan, jadi jangan dilaporkan.
        if ($keadaan === 'baru') {
            return self::SUCCESS;
        }

        $sebelumnya = Cache::get(self::KUNCI);

        if ($keadaan === $sebelumnya && ! $this->option('paksa')) {
            return self::SUCCESS;
        }

        Cache::put(self::KUNCI, $keadaan, now()->addDays(30));

        // Perubahan pertama setelah fitur dipasang: kalau keadaannya normal,
        // tidak perlu mengabari "semuanya baik-baik saja".
        if ($sebelumnya === null && $keadaan === 'normal' && ! $this->option('paksa')) {
            return self::SUCCESS;
        }

        $pesan = $this->pesan($keadaan, $sebelumnya);

        foreach ($chatIds as $id) {
            $telegram->kirim($id, $pesan);
        }

        $this->info('Peringatan terkirim: '.($sebelumnya ?? 'baru').' -> '.$keadaan);

        return self::SUCCESS;
    }

    private function pesan(string $keadaan, ?string $sebelumnya): string
    {
        $judul = match ($keadaan) {
            'normal' => $sebelumnya === null
                ? '✅ PEMICU TERJADWAL NORMAL'
                : '✅ PULIH — cron hPanel menyala lagi',
            'http' => '⚠️ CRON hPANEL MATI',
            'trafik' => '🟠 CRON hPANEL & CRON-JOB.ORG MATI',
            'mati' => '🔴 TIDAK ADA PEMICU YANG JALAN',
            default => 'ℹ️ Perubahan keadaan cron',
        };

        return $judul."\n".str_repeat('─', 24)."\n\n"
            .CronHeartbeat::ringkasan()
            ."\n\n"
            .match ($keadaan) {
                'normal' => 'Tidak ada tindakan yang perlu.',
                'http' => 'Tugas tetap jalan. Perbaiki cron hPanel saat sempat.',
                'trafik' => 'Perbaiki SEKARANG — malam hari saat situs sepi, '
                    .'pembayaran QRIS tidak akan terdeteksi.',
                'mati' => 'Kirim /cron_jalankan sebagai pertolongan pertama, '
                    .'lalu perbaiki cron hPanel.',
                default => '',
            };
    }
}
