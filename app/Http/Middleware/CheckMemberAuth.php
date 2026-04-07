<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CheckMemberAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (! data_get(Session::get('member'), 'id')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
