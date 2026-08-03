<?php

namespace App\Support;

use Illuminate\Support\Carbon;

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
 * PENYIMPANAN: berkas JSON di storage/app, BUKAN Cache.
 *
 * Versi pertama memakai Cache, dan itu keliru: CACHE_STORE=database, sehingga
 * `optimize:clear` (dipicu /cache_bersih dari bot) MENGHAPUS denyutnya. Efeknya
 * /cron langsung melaporkan "TIDAK ADA pemicu yang jalan" — berbohong tepat
 * saat admin sedang menelusuri masalah. Berkas tidak ikut terhapus
 * cache:clear, dan tetap tanpa migrasi/tabel baru.
 */
class CronHeartbeat
{
    /** Ambang basi: di atas ini cron dianggap tidak menyala. */
    public const AMBANG_DETIK = 300;

    /** Catat satu denyut. Dipanggil dari pemicu; menelan error sendiri. */
    public static function catat(string $sumber): void
    {
        try {
            $data = self::baca();
            $data[$sumber] = now()->toDateTimeString();

            // LOCK_EX supaya dua pemicu yang menembak bersamaan tidak saling
            // memotong isi berkas. Kalaupun satu denyut kalah balapan,
            // dampaknya cuma satu timestamp tertinggal semenit.
            @file_put_contents(self::berkas(), json_encode($data), LOCK_EX);
        } catch (\Throwable $e) {
            // Denyut nadi tidak boleh menjatuhkan scheduler atau request.
            report($e);
        }
    }

    /** Waktu denyut terakhir satu sumber ('cli'|'http'|'trafik'), null bila belum pernah. */
    public static function terakhir(string $sumber): ?Carbon
    {
        $nilai = self::baca()[$sumber] ?? null;

        if (! is_string($nilai) || $nilai === '') {
            return null;
        }

        try {
            return Carbon::parse($nilai);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function berkas(): string
    {
        return storage_path('app/cron-heartbeat.json');
    }

    /** @return array<string,string> */
    private static function baca(): array
    {
        try {
            $isi = @file_get_contents(self::berkas());

            if ($isi === false || $isi === '') {
                return [];
            }

            $data = json_decode($isi, true);

            return is_array($data) ? $data : [];
        } catch (\Throwable) {
            return [];
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
     * Keadaan ringkas, dipakai pemantau untuk mendeteksi PERUBAHAN keadaan.
     *
     * 'baru'   = belum ada denyut sama sekali (baru ter-deploy) — jangan
     *            dijadikan alasan mengirim peringatan.
     *
     * @return 'normal'|'http'|'trafik'|'mati'|'baru'
     */
    public static function keadaan(): string
    {
        if (self::sehat('cli')) {
            return 'normal';
        }
        if (self::sehat('http')) {
            return 'http';
        }
        if (self::sehat('trafik')) {
            return 'trafik';
        }

        $pernah = self::terakhir('cli') ?? self::terakhir('http') ?? self::terakhir('trafik');

        return $pernah === null ? 'baru' : 'mati';
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
