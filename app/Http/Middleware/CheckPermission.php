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
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if user has a role assigned
        if (!$user->role) {
            abort(403, 'No role assigned to user.');
        }

        // Check if user has any of the required permissions
        if (!$user->hasAnyPermission($permissions)) {
            abort(403, 'Unauthorized access. Required permission: ' . implode(', ', $permissions));
        }

        return $next($request);
    }
}
