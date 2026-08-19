<?php

namespace App\Http\Middleware;

use App\Support\PermintaanLivewire;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Jaring pengaman terakhir: permintaan LATAR Livewire tidak boleh pernah
 * dijawab dengan pengalihan.
 *
 * Livewire menangani pengalihan HTTP dengan `window.location.href = response.url`.
 * Pada permintaan latar (wire:poll, wire:click) itu membuat browser melompat
 * sendiri, dan kadang mendarat di /livewire/update yang HANYA menerima POST —
 * memunculkan "405 Method Not Allowed" di layar pengguna.
 *
 * Empat middleware kustom sudah menjaga jalurnya masing-masing, tetapi
 * pengalihan juga bisa datang dari tempat lain (mis. middleware `auth` bawaan
 * Laravel saat sesi habis). Middleware ini menutup sisanya sekaligus, tanpa
 * perlu menebak satu per satu.
 *
 * Aman: pengalihan yang dilakukan komponen Livewire sendiri TIDAK berbentuk
 * 302 — Livewire mengirimnya sebagai efek di dalam badan respons 200. Jadi
 * status 3xx pada permintaan Livewire selalu berasal dari middleware.
 */
class CegahAlihPermintaanLivewire
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (PermintaanLivewire::ya($request) && $response->isRedirection()) {
            return PermintaanLivewire::kedaluwarsa();
        }

        return $response;
    }
}
