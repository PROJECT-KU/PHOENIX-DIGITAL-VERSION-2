<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyAllowedOrigin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = trim((string) config('cors.allowed_origins.0'));
        $origin  = $request->headers->get('Origin');

        // Hanya izinkan jika Origin persis sama (dan tetap izinkan tools seperti curl: tanpa Origin => tolak)
        if (!$origin || !$allowed || !hash_equals($allowed, $origin)) {
            return response()->json(['message' => 'Forbidden origin'], 403);
        }
        return $next($request);
    }
}
