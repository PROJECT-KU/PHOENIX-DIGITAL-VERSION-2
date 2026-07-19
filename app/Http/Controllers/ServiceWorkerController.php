<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class ServiceWorkerController extends Controller
{
    /**
     * Sajikan /sw.js dengan versi cache OTOMATIS mengikuti build.
     *
     * Versi diambil dari hash manifest Vite (public/build/manifest.json) yang
     * berubah setiap `npm run build`. Jadi tiap deploy → isi sw.js berubah →
     * browser mendeteksi service worker baru → cache lama dibuang sendiri.
     * Tidak perlu menaikkan versi secara manual.
     */
    public function __invoke(): Response
    {
        $template = file_get_contents(resource_path('sw.js'));

        $manifest = public_path('build/manifest.json');
        $version = is_file($manifest)
            ? substr(md5_file($manifest), 0, 12)
            : substr(md5((string) @filemtime(resource_path('sw.js'))), 0, 12);

        $body = str_replace('__SW_VERSION__', $version, $template);

        return response($body, 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Service-Worker-Allowed' => '/',
        ]);
    }
}
