<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LastUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // opsional: hanya update jika lebih dari 10 detik sejak terakhir update
            // ini mengurangi I/O DB
            $thresholdSeconds = 10;

            if (! $user->last_seen_at || $user->last_seen_at->lt(now()->subSeconds($thresholdSeconds))) {
                $user->last_seen_at = now();
                $user->save();

                // debug log (hapus atau komentari setelah testing)
                // Log::info("LastUserActivity: updated last_seen_at for user {$user->id}");
            }
        }

        return $next($request);
    }
}
