<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pemicu scheduler berbasis trafik ("poor man's cron").
 *
 * Dibuat untuk shared hosting yang tidak mudah mengatur cron: selama situs
 * dikunjungi, tugas terjadwal (mis. membatalkan order kedaluwarsa, promo)
 * tetap berjalan — tanpa perlu menambah cron atau menjalankan perintah apa pun
 * setelah deploy dari GitHub.
 *
 * - Dijalankan di terminate() → SETELAH respons terkirim, jadi tidak menambah
 *   waktu tunggu pengunjung.
 * - Dibatasi maksimal sekali per ~menit lewat atomic lock, jadi tidak berjalan
 *   berkali-kali walau trafik ramai.
 * - Bila nanti cron asli dipasang di server, matikan lewat .env:
 *   SCHEDULER_TRAFFIC_KICK=false
 */
class KickScheduler
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! config('app.scheduler_traffic_kick', true)) {
            return;
        }

        try {
            // Lock ditahan ~58 dtk (tidak di-release) sebagai pembatas laju:
            // hanya request pertama tiap menit yang benar-benar menjalankan scheduler.
            $lock = Cache::lock('kick-scheduler', 58);

            if ($lock->get()) {
                Artisan::call('schedule:run');
            }
        } catch (\Throwable $e) {
            // Jangan pernah mengganggu request karena ini hanya proses latar.
            report($e);
        }
    }
}
