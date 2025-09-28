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

                return redirect('/login')
                    ->with('idle_timeout', 'Sesi Anda berakhir karena tidak ada aktivitas.');
            }

            session(['lastActivityTime' => time()]);
        }

        return $next($request);
    }
}
