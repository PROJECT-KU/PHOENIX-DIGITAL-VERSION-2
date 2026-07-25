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

        $log = [];

        try {
            // Jalur normal: scheduler penuh (menghormati jadwal & withoutOverlapping).
            Artisan::call('schedule:run');
            $log[] = 'schedule:run OK';
        } catch (\Throwable $e) {
            // Sebagian shared host memblokir proc_open di SAPI web — schedule:run
            // butuh itu untuk men-spawn command. Fallback: jalankan tugas KRITIS
            // per-menit langsung in-process (tak butuh proc_open).
            $log[] = 'schedule:run gagal: '.$e->getMessage().' → fallback in-process';
            foreach (['qris:cek-pembayaran', 'orders:cancel-expired', 'payment:remind'] as $cmd) {
                try {
                    Artisan::call($cmd);
                    $log[] = $cmd.' OK';
                } catch (\Throwable $e2) {
                    $log[] = $cmd.' ERROR: '.$e2->getMessage();
                }
            }
        }

        return response(implode("\n", $log)."\n", 200)
            ->header('Content-Type', 'text/plain');
    }
}
