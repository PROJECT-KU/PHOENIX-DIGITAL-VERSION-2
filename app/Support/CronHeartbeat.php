<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Denyut nadi scheduler — supaya "cron mati" bisa DIBUKTIKAN, bukan ditebak.
 *
 * Proyek ini punya TIGA pemicu schedule:run, dan masing-masing mencatat
 * denyutnya sendiri supaya bisa dibedakan:
 *
 *   - 'cli'    => cron asli hPanel (`php artisan schedule:run` dari konsol).
 *                 Pemicu UTAMA, diharapkan menyala tiap menit.
 *   - 'http'   => cron-job.org memanggil /cron/run/{token} (CronController).
 *                 Jaring pengaman ANDAL: jalan tiap menit tanpa peduli trafik.
 *   - 'trafik' => KickScheduler, menumpang request pengunjung.
 *                 Jaring pengaman RAPUH: berhenti kalau situs sepi.
 *
 * Membedakan 'http' dari 'trafik' itu penting: kalau keduanya dilebur, matinya
 * cron-job.org akan tertutupi oleh trafik dan tidak pernah terlihat — padahal
 * situs sepi di malam hari, justru saat order QRIS perlu dicek.
 *
 * Sengaja pakai Cache (driver database) supaya tidak menambah tabel/migrasi.
 * TTL 7 hari: cukup lama untuk mendeteksi mati berhari-hari.
 */
class CronHeartbeat
{
    /** Ambang basi: di atas ini cron dianggap tidak menyala. */
    public const AMBANG_DETIK = 300;

    private const TTL_HARI = 7;

    /** Catat satu denyut. Dipanggil dari scheduler; menelan error sendiri. */
    public static function catat(string $sumber): void
    {
        try {
            $waktu = now()->toDateTimeString();

            Cache::put('cron:heartbeat', $waktu, now()->addDays(self::TTL_HARI));
            Cache::put('cron:heartbeat:'.$sumber, $waktu, now()->addDays(self::TTL_HARI));
        } catch (\Throwable $e) {
            // Denyut nadi tidak boleh menjatuhkan scheduler.
            report($e);
        }
    }

    /** Waktu denyut terakhir untuk satu sumber ('cli'|'web'), null bila belum pernah. */
    public static function terakhir(?string $sumber = null): ?Carbon
    {
        $kunci = $sumber ? 'cron:heartbeat:'.$sumber : 'cron:heartbeat';

        try {
            $nilai = Cache::get($kunci);
        } catch (\Throwable $e) {
            return null;
        }

        if (! $nilai) {
            return null;
        }

        try {
            return Carbon::parse($nilai);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** True bila satu sumber menyala dalam ambang waktu. */
    public static function sehat(string $sumber): bool
    {
        $waktu = self::terakhir($sumber);

        return $waktu !== null && $waktu->diffInSeconds(now()) <= self::AMBANG_DETIK;
    }

    /** True bila cron server (hPanel) menyala. Pemicu utama. */
    public static function cronServerSehat(): bool
    {
        return self::sehat('cli');
    }

    /**
     * Ringkasan siap-kirim untuk bot Telegram.
     *
     * Kesimpulan di baris pertama sengaja menjawab pertanyaan yang sebenarnya:
     * "apakah tugas terjadwal masih dieksekusi, dan seberapa bisa diandalkan?"
     * — bukan sekadar "cron hPanel hidup/mati".
     */
    public static function ringkasan(): string
    {
        $cli = self::sehat('cli');
        $http = self::sehat('http');
        $trafik = self::sehat('trafik');

        $baris = [match (true) {
            $cli => '✅ Terjadwal NORMAL — cron hPanel menyala',
            $http => '⚠️ Cron hPanel MATI — ditopang cron-job.org',
            $trafik => '🟠 Cron hPanel & cron-job.org MATI — hanya ditopang trafik pengunjung',
            default => '🔴 TIDAK ADA pemicu yang jalan',
        }];

        $baris[] = '';
        $baris[] = ($cli ? '✅' : '🔴').' Cron hPanel: '.self::jarak(self::terakhir('cli'));
        $baris[] = ($http ? '✅' : '🔴').' cron-job.org: '.self::jarak(self::terakhir('http'));
        $baris[] = ($trafik ? '✅' : '⚪').' Trafik pengunjung: '.self::jarak(self::terakhir('trafik'));

        $baris[] = '';
        $baris[] = match (true) {
            $cli && $http => 'Keduanya sehat. Aman — keduanya boleh jalan bersamaan, '
                .'tugasnya idempoten dan dikunci withoutOverlapping.',
            $cli => 'Pemicu utama sehat. cron-job.org sedang tidak menembak — '
                .'periksa kalau memang sengaja dipasang sebagai cadangan.',
            $http => 'Tugas terjadwal TETAP jalan tiap menit lewat cron-job.org, jadi order '
                .'QRIS tetap terdeteksi. Tapi perbaiki cron hPanel — jangan bergantung '
                .'pada satu jaring pengaman saja.',
            $trafik => 'RAWAN: hanya jalan saat ada pengunjung. Saat situs sepi (malam hari) '
                .'pembayaran QRIS tidak akan terdeteksi dan order kedaluwarsa tidak '
                .'dibatalkan. Perbaiki cron hPanel atau cron-job.org sekarang.',
            default => 'Tugas terjadwal TIDAK berjalan sama sekali. Pakai /cron_jalankan '
                .'sebagai pertolongan pertama, lalu perbaiki cron hPanel.',
        };

        if (self::terakhir('cli') === null && self::terakhir('http') === null && self::terakhir('trafik') === null) {
            $baris[] = '';
            $baris[] = 'ℹ️ Belum ada denyut sama sekali — wajar bila fitur ini baru ter-deploy. '
                .'Tunggu sampai scheduler jalan sekali.';
        }

        return implode("\n", $baris);
    }

    /**
     * "2 menit yang lalu (03/08 16:45)" atau "belum pernah".
     *
     * locale('id') dipaksa karena APP_LOCALE=en di .env — sama pola dengan
     * accessor tanggal di model (lihat CLAUDE.md).
     */
    private static function jarak(?Carbon $waktu): string
    {
        if ($waktu === null) {
            return 'belum pernah';
        }

        return $waktu->locale('id')->diffForHumans()
            .' ('.$waktu->format('d/m H:i').')';
    }
}
