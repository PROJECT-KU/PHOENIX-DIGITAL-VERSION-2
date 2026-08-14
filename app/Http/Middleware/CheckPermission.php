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

        // Check if user has a role assigned
        if (! $user->role) {
            abort(403, 'No role assigned to user.');
        }

        // Check if user has any of the required permissions
        if (! $user->hasAnyPermission($permissions)) {
            abort(403, 'Unauthorized access. Required permission: '.implode(', ', $permissions));
        }

        return $next($request);
    }
}
