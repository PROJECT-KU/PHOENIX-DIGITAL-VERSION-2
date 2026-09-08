<?php

namespace App\Http\Middleware;

use App\Support\FiturAdmin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menahan modul admin yang sedang ditutup.
 *
 * Pemegang izin "Kelola Jeda Layanan" sengaja dilewatkan: dialah yang
 * mengerjakan perbaikannya, dan kalau ia ikut terkunci tak ada yang bisa
 * memastikan modulnya sudah benar sebelum dibuka untuk yang lain — juga tak ada
 * yang bisa membukanya kembali.
 */
class CekFiturAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $fitur = FiturAdmin::dariRute($request->route()?->getName());

        if (! $fitur || ! FiturAdmin::ditutup($fitur)) {
            return $next($request);
        }

        if (auth()->user()?->hasPermission('manage_jeda_layanan')) {
            return $next($request);
        }

        return response()->view('admin.fitur-ditutup', [
            'judul' => FiturAdmin::label($fitur),
            'pesan' => FiturAdmin::pesan($fitur),
            'mulai' => FiturAdmin::mulai($fitur),
            'sampai' => FiturAdmin::sampai($fitur),
        ], 503);
    }
}
