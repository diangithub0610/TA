<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        // Check admin guard
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            if (in_array($user->role, $roles)) {
                return $next($request);
            }
        }

        // Check pelanggan guard
        if (Auth::guard('pelanggan')->check()) {
            $user = Auth::guard('pelanggan')->user();
            if (in_array($user->role, $roles)) {
                return $next($request);
            }
        }

        // Unauthorized
        return redirect('login')->with('error', 'Anda tidak memiliki akses');
    }
}
