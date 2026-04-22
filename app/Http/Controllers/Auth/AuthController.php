<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Support\MemberSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function login()
    {
        // If already logged in, redirect to dashboard
        if (Session::has('member')) {
            return redirect()->route('dashboard');
        }

        return response()
            ->view('pages.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    /**
     * Handle login form submission.
     */
    public function authenticate(LoginRequest $request)
    {
        $memberId = trim((string) $request->member_id);
        $password = (string) $request->password;

        $member = DB::table('T_MEMBER')
            ->where('tx_org_id', $memberId)
            ->where('tx_password', $password)
            ->select('tx_org_id', 'tx_name')
            ->first();

        if (! $member) {
            return back()
                ->withInput(['member_id' => $memberId])
                ->withErrors(['member_id' => 'Invalid Membership ID or password.']);
        }

        $request->session()->regenerate();
        $request->session()->regenerateToken();
        $request->session()->put(
            MemberSession::KEY,
            MemberSession::build($member->tx_org_id, $member->tx_name)
        );

        return redirect()->route('dashboard');
    }

    /**
     * Log the member out.
     */
    public function logout(Request $request)
    {
        MemberSession::logout($request);

        return redirect()->route('login');
    }
}
