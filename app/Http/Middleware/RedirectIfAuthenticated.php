<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{

    public function handle(Request $request, Closure $next): Response
    {
        if (Session::has('member')) {
            return redirect()->route('dashboard');
        }
        return $next($request);
    }
}
