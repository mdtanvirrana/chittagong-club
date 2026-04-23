<?php

namespace App\Http\Middleware;

use App\Support\MemberSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{

    public function handle(Request $request, Closure $next): Response
    {
        $member = Session::get(MemberSession::KEY);

        if (! data_get($member, 'id')) {
            return $next($request);
        }

        if (MemberSession::needsExpiryRefresh($member)) {
            MemberSession::refreshExpiry($request);
            $member = Session::get(MemberSession::KEY);
        }

        if (MemberSession::isExpired($member)) {
            MemberSession::logout($request);
            $request->session()->flash('session_expired', MemberSession::EXPIRY_MESSAGE);

            return $next($request);
        }

        if (Session::has(MemberSession::KEY)) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
