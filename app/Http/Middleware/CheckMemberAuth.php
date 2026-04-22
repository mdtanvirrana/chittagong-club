<?php

namespace App\Http\Middleware;

use App\Support\MemberSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CheckMemberAuth
{
    public function handle(Request $request, Closure $next)
    {
        $member = Session::get(MemberSession::KEY);

        if (! data_get($member, 'id')) {
            return redirect()->route('login');
        }

        if (MemberSession::needsExpiryRefresh($member)) {
            MemberSession::refreshExpiry($request);
            $member = Session::get(MemberSession::KEY);
        }

        if (MemberSession::isExpired($member)) {
            MemberSession::logout($request);

            return redirect()
                ->route('login')
                ->with('session_expired', MemberSession::EXPIRY_MESSAGE);
        }

        return $next($request);
    }
}
