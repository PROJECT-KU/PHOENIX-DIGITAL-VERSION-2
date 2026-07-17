<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureGuestToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasCookie('guest_token')) {
            $token = (string) Str::uuid();

            Cookie::queue('guest_token', $token, 2628000);

            // Sediakan juga di request yang sama, supaya order yang dibuat pada
            // kunjungan pertama (sebelum cookie bolak-balik) tetap mendapat token
            // kepemilikan — bukan null. Ini yang mengunci akses lintas-browser.
            $request->cookies->set('guest_token', $token);
        }

        return $next($request);
    }
}
