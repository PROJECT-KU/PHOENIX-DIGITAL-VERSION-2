<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        // Permintaan LATAR Livewire (mis. wire:poll tiap 5 detik di halaman admin)
        // tidak boleh dijawab dengan pengalihan: Livewire menanggapinya dengan
        // `window.location.href = response.url`, sehingga admin tiba-tiba
        // terlempar tanpa menyentuh apa pun. 419 membuat Livewire memuat ulang
        // halaman yang sedang dibuka secara wajar.
        if (\App\Support\PermintaanLivewire::ya($request)) {
            return \App\Support\PermintaanLivewire::kedaluwarsa();
        }

        // Check if user is authenticated
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        /*
         | Peran diperiksa SESUDAH izinnya, bukan sebelum.
         |
         | Sejak izin bisa diberikan per orang (tabel permission_user),
         | "tidak punya peran" bukan lagi jawaban akhir — orangnya mungkin
         | memang diberi izin itu secara pribadi. Menolak lebih dulu berarti
         | menutup pintu tanpa pernah memeriksa kuncinya.
         |
         | Pesannya tetap dibedakan: "belum punya peran" dan "perannya tidak
         | mencakup izin ini" adalah dua keadaan berbeda, dan yang membaca
         | log perlu tahu yang mana.
         */
        if (! $user->hasAnyPermission($permissions)) {
            abort(403, $user->role
                ? 'Unauthorized access. Required permission: '.implode(', ', $permissions)
                : 'No role assigned to user.');
        }

        return $next($request);
    }
}
