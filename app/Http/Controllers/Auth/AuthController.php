<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
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

        return view('pages.login');
    }

    /**
     * Handle login form submission.
     */
    public function authenticate(LoginRequest $request)
    {

        $member = DB::table('T_MEMBER')
            ->where('tx_org_id',   $request->member_id)
            ->where('tx_password', $request->password)
            ->first();

        if (! $member) {
            return back()
                ->withInput($request->only('member_id'))
                ->withErrors(['member_id' => 'Invalid Membership ID or password.']);
        }

        // Store member info in session
        Session::put('member', [
            'id'   => $member->tx_org_id,
            'name' => $member->tx_name ?? 'Member',   // adjust column name if different
        ]);

        return redirect()->route('dashboard');
    }

    /**
     * Log the member out.
     */
    public function logout(Request $request)
    {
        Session::forget('member');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
