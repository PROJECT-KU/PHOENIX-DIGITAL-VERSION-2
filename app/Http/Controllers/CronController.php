<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

/**
 * Pemicu cron CADANGAN lewat HTTP. Dipakai layanan cron eksternal (mis.
 * cron-job.org) untuk memanggil scheduler tiap menit BILA cron hPanel mati.
 *
 * Aman: hanya berjalan bila token cocok; token kosong => 404 (nonaktif).
 */
class CronController extends Controller
{
    public function __invoke(string $token)
    {
        $expected = config('cron.token');

        abort_if(empty($expected), 404);
        abort_unless(hash_equals($expected, (string) $token), 404);

        // Tandai bahwa pemicu HTTP (cron-job.org) menembak. Dicatat terpisah
        // dari pemicu trafik supaya /cron di bot Telegram bisa membedakan
        // "cadangan andal masih jalan" dari "cuma kebetulan ada pengunjung".
        \App\Support\CronHeartbeat::catat('http');

        $log = [];

        // Tugas KRITIS (deteksi bayar QRIS & cancel order kedaluwarsa) dijalankan
        // LANGSUNG in-process, BUKAN lewat schedule:run. Sebab lewat scheduler,
        // mutex withoutOverlapping yang NYANGKUT membuat command di-skip DIAM-DIAM
        // (schedule:run tak melempar error, jadi fallback lama tak terpicu) →
        // pembayaran QRIS tak terdeteksi walau URL cron dipicu. In-process =
        // tanpa mutex, DIJAMIN jalan. Idempoten, jadi aman meski juga jalan lewat
        // cron hPanel.
        foreach (['qris:cek-pembayaran', 'orders:cancel-expired'] as $cmd) {
            try {
                Artisan::call($cmd);
                $out = trim(Artisan::output());
                $log[] = $cmd.' OK'.($out !== '' ? ' — '.str_replace("\n", ' | ', $out) : '');
            } catch (\Throwable $e) {
                $log[] = $cmd.' ERROR: '.$e->getMessage();
            }
        }

        // Sisanya (aktivasi promo, pengingat bayar, prune harian, notif task, dsb)
        // lewat scheduler penuh yang menghormati jadwal masing-masing.
        try {
            Artisan::call('schedule:run');
            $log[] = 'schedule:run OK';
        } catch (\Throwable $e) {
            $log[] = 'schedule:run gagal: '.$e->getMessage();
        }

        return response(implode("\n", $log)."\n", 200)
            ->header('Content-Type', 'text/plain');
    }
}
