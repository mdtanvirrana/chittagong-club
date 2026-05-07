<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function create()
    {
        return response()
            ->view('admin.auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    public function store(LoginRequest $request)
    {
        $login = trim((string) $request->input('login'));
        $password = (string) $request->input('password');

        $adminRecord = DB::table('Users_App')
            ->where('PrvcusID', $login)
            ->where('is_admin', 1)
            ->first();

        if (! $adminRecord) {
            return back()
                ->withInput(['login' => $login])
                ->withErrors(['login' => 'Invalid credentials']);
        }

        $admin = new AdminUser((array) $adminRecord);
        $admin->exists = true;

        if (! $admin->passwordMatches($password)) {
            return back()
                ->withInput(['login' => $login])
                ->withErrors(['login' => 'Invalid credentials']);
        }

        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
