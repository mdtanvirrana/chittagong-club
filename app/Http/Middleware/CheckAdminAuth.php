<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        $guard = Auth::guard('admin');

        if (! $guard->check()) {
            return redirect()->route('admin.login');
        }

        if (! $guard->user()?->hasAdminAccess()) {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->withErrors(['login' => 'Admin access is disabled for this account.']);
        }

        return $next($request);
    }
}
