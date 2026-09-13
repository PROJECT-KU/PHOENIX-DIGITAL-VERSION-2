<?php

namespace App\Http\Middleware;

use App\Support\BotTurnitin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gerbang API bot Turnitin: token Bearer yang dibuat admin di dashboard.
 * Yang disimpan hanya hash-nya; token polos tidak pernah tersimpan di server.
 */
class TokenBotTurnitin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! BotTurnitin::skemaSiap()) {
            return response()->json(['ok' => false, 'pesan' => 'Database Phoenix belum diperbarui untuk bot (SQL belum dijalankan).'], 503);
        }

        if (! BotTurnitin::tokenCocok($request->bearerToken())) {
            return response()->json(['ok' => false, 'pesan' => 'Token bot tidak valid. Buat token baru di dashboard admin.'], 401);
        }

        return $next($request);
    }
}
