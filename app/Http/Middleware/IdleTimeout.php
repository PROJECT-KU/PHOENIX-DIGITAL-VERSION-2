<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IdleTimeout
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $timeout = 3000; // 5 menit
            $lastActivity = session('lastActivityTime');

            if ($lastActivity && (time() - $lastActivity > $timeout)) {
                Auth::logout();
                session()->flush();

                // Permintaan LATAR Livewire (wire:poll tiap 5 detik di halaman
                // admin) tidak boleh dijawab dengan pengalihan — Livewire
                // menanggapinya dengan `window.location.href = response.url`,
                // sehingga admin tiba-tiba terlempar tanpa menyentuh apa pun.
                // 419 membuat Livewire memuat ulang halaman yang sedang dibuka,
                // lalu pengalihan ke login terjadi pada permintaan halaman biasa.
                if (\App\Support\PermintaanLivewire::ya($request)) {
                    return \App\Support\PermintaanLivewire::kedaluwarsa();
                }

                return redirect('/login')
                    ->with('idle_timeout', 'Sesi Anda berakhir karena tidak ada aktivitas.');
            }

            session(['lastActivityTime' => time()]);
        }

        return $next($request);
    }
}
