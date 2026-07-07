<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('admin');
        $isAuthenticated = $guard->check();

        if ($isAuthenticated && $guard->user()?->hasAdminAccess()) {
            return redirect()->route('admin.dashboard');
        }

        if ($isAuthenticated) {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $next($request);
    }
}
