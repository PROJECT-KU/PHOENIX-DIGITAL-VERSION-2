<?php

namespace App\Http\Middleware;

use App\Support\FiturPublik;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menahan halaman publik yang sedang ditutup admin.
 *
 * Dipasang di grup web dan menanyakan NAMA RUTE ke FiturPublik, bukan mencocokkan
 * URL sendiri. Dengan begitu menambah halaman publik baru cukup mendaftarkannya
 * di FiturPublik::DAFTAR — berkas ini tidak perlu disentuh lagi.
 *
 * Admin yang sudah masuk sengaja dilewatkan: ia perlu melihat halamannya untuk
 * memastikan perbaikannya selesai sebelum dibuka kembali untuk umum.
 */
class CekFiturPublik
{
    public function handle(Request $request, Closure $next): Response
    {
        $fitur = FiturPublik::dariRute($request->route()?->getName());

        if ($fitur && FiturPublik::ditutup($fitur) && ! auth()->check()) {
            // 503, bukan 404: halamannya ada, hanya sedang tidak dilayani —
            // mesin pencari akan kembali lagi alih-alih membuang alamatnya.
            return response()->view('publik.fitur-ditutup', [
                'judul' => FiturPublik::label($fitur),
                'pesan' => FiturPublik::pesan($fitur),
            ], 503);
        }

        return $next($request);
    }
}
