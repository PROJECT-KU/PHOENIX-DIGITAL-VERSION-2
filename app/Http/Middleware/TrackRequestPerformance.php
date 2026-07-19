<?php

namespace App\Http\Middleware;

use App\Support\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Mencatat setiap KUNJUNGAN halaman (public & admin) ke Log Aktivitas beserta
 * durasinya — untuk tahu traffic & URL mana yang lambat.
 *
 * Pengukuran & penulisan dilakukan di terminate() — yang berjalan SETELAH
 * response dikirim ke pengguna — sehingga tidak menambah waktu tunggu sama
 * sekali. handle() hanya meneruskan request tanpa mengubah apa pun.
 *
 * Hanya page view sungguhan yang dicatat (GET + respons HTML + sukses); aset,
 * request AJAX/Livewire, dan endpoint internal dilewati agar log tidak banjir.
 */
class TrackRequestPerformance
{
    /** Path yang tak perlu dicatat (infrastruktur / polling / diri sendiri). */
    private const LEWATI_PATH = [
        'livewire', 'sw.js', 'manifest', 'up', 'sitemap', 'robots',
        'favicon', 'notifications', 'admin/activity-log',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            if (! defined('LARAVEL_START')) {
                return;
            }
            if (! $this->layakDicatat($request, $response)) {
                return;
            }

            $ms = (int) round((microtime(true) - LARAVEL_START) * 1000);
            ActivityLogger::visit($ms, $response->getStatusCode());
        } catch (Throwable) {
            // Logging tak boleh mengganggu apa pun.
        }
    }

    /** Hanya page view HTML yang sukses; sisanya (aset/AJAX/error) dilewati. */
    private function layakDicatat(Request $request, Response $response): bool
    {
        // Hanya navigasi halaman (GET). Aksi POST/PUT dsb. tidak dihitung kunjungan.
        if (! $request->isMethod('GET')) {
            return false;
        }

        // Update Livewire / AJAX / permintaan JSON bukan "kunjungan halaman".
        if ($request->hasHeader('X-Livewire') || $request->ajax() || $request->wantsJson()) {
            return false;
        }

        // Hanya respons HTML (aset gambar/js/css/JSON otomatis terlewati).
        $contentType = (string) $response->headers->get('Content-Type', '');
        if (! str_contains($contentType, 'text/html')) {
            return false;
        }

        // Error (4xx/5xx) sudah ditangani jalur lain (exception logger); di sini
        // fokus kunjungan sukses agar tidak dobel & tidak mencatat 404 sampah.
        if ($response->getStatusCode() >= 400) {
            return false;
        }

        $path = $request->path();
        foreach (self::LEWATI_PATH as $lewati) {
            if (str_contains($path, $lewati)) {
                return false;
            }
        }

        return true;
    }
}
