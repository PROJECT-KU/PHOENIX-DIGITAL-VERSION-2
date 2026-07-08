<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    /**
     * Karyawan (selain Admin) wajib melengkapi profil (rekening, tanggal lahir,
     * no HP, alamat) sebelum mengakses fitur panel admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user
            && ! $user->hasRole('admin')
            && $request->routeIs('admin.*')
            && ! $request->routeIs('admin.account.profile')
            && ! $user->profileComplete()
        ) {
            return redirect()->route('admin.account.profile')
                ->with('profile_incomplete', 'Lengkapi profil Anda (No. Rekening, Tanggal Lahir, No. HP, dan Alamat) untuk dapat mengakses fitur.');
        }

        return $next($request);
    }
}
