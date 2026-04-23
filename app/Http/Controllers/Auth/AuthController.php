<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\InitializePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Support\MemberAccess;
use App\Support\MemberSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    private const INITIAL_PASSWORD_SESSION_KEY = 'member_initial_password_setup';

    /**
     * Show the login form.
     */
    public function login()
    {
        // If already logged in, redirect to dashboard
        if (Session::has('member')) {
            return redirect()->route('dashboard');
        }

        return $this->guestView('pages.login');
    }

    /**
     * Handle login form submission.
     */
    public function authenticate(LoginRequest $request)
    {
        $memberId = trim((string) $request->member_id);
        $password = (string) $request->password;

        $member = MemberAccess::findActiveMember($memberId, [
            'c.PrvCusID',
            'c.Title',
            'c.CusName',
        ]);

        if (! $member) {
            return back()
                ->withInput(['member_id' => $memberId])
                ->withErrors(['member_id' => 'Invalid Membership ID or password.']);
        }

        if (MemberAccess::passwordSetupRequired($memberId)) {
            $request->session()->put(self::INITIAL_PASSWORD_SESSION_KEY, [
                'member_id' => $member->PrvCusID,
                'member_name' => MemberAccess::displayName($member),
            ]);
            $request->session()->regenerateToken();

            return redirect()
                ->route('password.initial.create')
                ->with('password_setup_status', 'No password is set for this member ID. Create a new password to continue.');
        }

        if (! MemberAccess::credentialsMatch($memberId, $password)) {
            return back()
                ->withInput(['member_id' => $memberId])
                ->withErrors(['member_id' => 'Invalid Membership ID or password.']);
        }

        $request->session()->regenerate();
        $request->session()->regenerateToken();
        $request->session()->put(
            MemberSession::KEY,
            MemberSession::build($member->PrvCusID, MemberAccess::displayName($member))
        );
        MemberAccess::recordActivity($member->PrvCusID, 'Login', $request->ip());

        return redirect()->route('dashboard');
    }

    public function showInitialPasswordSetup(Request $request)
    {
        $state = $this->initialPasswordState($request);

        if (! $state) {
            return redirect()->route('login');
        }

        if (! MemberAccess::passwordSetupRequired((string) data_get($state, 'member_id'))) {
            $this->clearInitialPasswordState($request);

            return redirect()
                ->route('login')
                ->with('password_reset_status', 'Password already exists. Sign in with your current password.');
        }

        return $this->guestView('pages.initial-password', [
            'setupState' => $state,
        ]);
    }

    public function storeInitialPassword(InitializePasswordRequest $request)
    {
        $state = $this->initialPasswordState($request);

        if (! $state) {
            return redirect()
                ->route('login')
                ->withErrors(['member_id' => 'Start from the member login page to set your first password.']);
        }

        $memberId = trim((string) data_get($state, 'member_id'));

        if (! MemberAccess::activeMemberExists($memberId)) {
            $this->clearInitialPasswordState($request);

            return redirect()
                ->route('login')
                ->withErrors(['member_id' => 'Member not found.']);
        }

        if (! MemberAccess::passwordSetupRequired($memberId)) {
            $this->clearInitialPasswordState($request);

            return redirect()
                ->route('login')
                ->with('password_reset_status', 'Password already exists. Sign in with your current password.');
        }

        $updated = MemberAccess::changePassword(
            $memberId,
            (string) $request->input('password'),
            'new'
        );

        if (! $updated) {
            return back()->withErrors([
                'password' => 'Unable to save the password right now. Please try again.',
            ]);
        }

        $this->clearInitialPasswordState($request);
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('password_reset_status', 'Password created successfully. Sign in with your new password.');
    }

    /**
     * Log the member out.
     */
    public function logout(Request $request)
    {
        $memberId = trim((string) data_get($request->session()->get(MemberSession::KEY), 'id'));

        if ($memberId !== '') {
            MemberAccess::recordActivity($memberId, 'Logout', $request->ip());
        }

        MemberSession::logout($request);

        return redirect()->route('login');
    }

    private function guestView(string $view, array $data = [])
    {
        return response()
            ->view($view, $data)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    private function initialPasswordState(Request $request): ?array
    {
        $state = $request->session()->get(self::INITIAL_PASSWORD_SESSION_KEY);

        if (! is_array($state) || ! filled(data_get($state, 'member_id'))) {
            return null;
        }

        return $state;
    }

    private function clearInitialPasswordState(Request $request): void
    {
        $request->session()->forget(self::INITIAL_PASSWORD_SESSION_KEY);
    }
}
