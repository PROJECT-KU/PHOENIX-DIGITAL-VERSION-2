<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Denyut nadi scheduler — supaya "cron mati" bisa DIBUKTIKAN, bukan ditebak.
 *
 * Dicatat dari satu Schedule::call di routes/console.php, jadi ikut jalan pada
 * SETIAP pemicu schedule:run, apa pun sumbernya. Sumbernya dibedakan dari
 * konteks proses:
 *
 *   - 'cli' => proses konsol = cron asli hPanel/server. INI yang diharapkan
 *              menyala tiap menit. Kalau basi (> 5 menit), cron hPanel mati.
 *   - 'web' => dipicu request HTTP: KickScheduler (trafik pengunjung) atau
 *              route /cron/run/{token}. Ini jaring pengaman, bukan pengganti.
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

    /** True bila cron server (CLI) menyala dalam ambang waktu. */
    public static function cronServerSehat(): bool
    {
        $cli = self::terakhir('cli');

        return $cli !== null && $cli->diffInSeconds(now()) <= self::AMBANG_DETIK;
    }

    /** Ringkasan siap-kirim untuk bot Telegram. */
    public static function ringkasan(): string
    {
        $baris = [];

        $cli = self::terakhir('cli');
        $web = self::terakhir('web');

        $baris[] = self::cronServerSehat()
            ? '✅ Cron server (CLI) NORMAL'
            : '🔴 Cron server (CLI) TIDAK MENYALA';

        $baris[] = '• Cron server terakhir: '.self::jarak($cli);
        $baris[] = '• Pemicu web terakhir: '.self::jarak($web);

        if (! self::cronServerSehat() && $web !== null && $web->diffInSeconds(now()) <= self::AMBANG_DETIK) {
            $baris[] = '';
            $baris[] = 'ℹ️ Jaring pengaman (trafik/URL cron) masih jalan, jadi tugas terjadwal '
                .'tetap dieksekusi. Tapi cron hPanel perlu diperbaiki — jaring pengaman '
                .'berhenti bila situs sepi pengunjung.';
        }

        if ($cli === null && $web === null) {
            $baris[] = '';
            $baris[] = 'ℹ️ Belum ada denyut sama sekali. Wajar bila fitur ini baru dipasang — '
                .'tunggu sampai scheduler jalan sekali.';
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
