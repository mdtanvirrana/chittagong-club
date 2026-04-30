<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\HandlesMemberOtp;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\InitializePasswordRequest;
use App\Http\Requests\Auth\VerifyPasswordResetCodeRequest;
use App\Http\Requests\LoginRequest;
use App\Services\Auth\RobiSmsService;
use App\Support\MemberAccess;
use App\Support\MemberSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    use HandlesMemberOtp;

    private const INITIAL_PASSWORD_SESSION_KEY = 'member_initial_password_setup';
    private const OTP_TTL_MINUTES = 5;
    private const MAX_OTP_ATTEMPTS = 5;

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
            'c.Mobile',
            'c.Phone',
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
                'phone' => MemberAccess::registeredPhone($member),
                'otp_id' => null,
                'sent_at' => null,
                'expires_at' => null,
                'attempts' => 0,
                'verified_at' => null,
                'verified_until' => null,
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

        if ($this->initialPasswordOtpVerified($state)) {
            return $this->guestView('pages.initial-password', [
                'setupState' => $state,
            ]);
        }

        if ($this->initialPasswordOtpPending($state)) {
            return $this->guestView('pages.initial-password-otp', [
                'setupState' => $state,
            ]);
        }

        return $this->guestView('pages.initial-password-start', [
            'setupState' => $state,
            'hasRegisteredPhone' => filled(data_get($state, 'phone.e164_digits')),
        ]);
    }

    public function sendInitialPasswordOtp(Request $request, RobiSmsService $robiSms)
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

        $phone = data_get($state, 'phone');

        if (! is_array($phone) || ! filled(data_get($phone, 'e164_digits'))) {
            return redirect()
                ->route('password.initial.create')
                ->withErrors(['phone' => 'No registered Bangladesh mobile number was found for this member ID. Contact the club office.']);
        }

        if ((int) data_get($state, 'otp_id') > 0 && ! $this->initialPasswordOtpVerified($state)) {
            $this->updateOtpStatus((int) data_get($state, 'otp_id'), 'RESENT');
        }

        try {
            $otpState = $this->dispatchOtp(
                $phone,
                [[
                    'member_id' => $memberId,
                    'member_name' => data_get($state, 'member_name', 'Member'),
                ]],
                $robiSms,
                'new'
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('password.initial.create')
                ->withErrors(['phone' => 'We could not send the code right now. Please try again shortly.']);
        }

        $state = [
            ...$state,
            ...$otpState,
        ];

        $request->session()->put(self::INITIAL_PASSWORD_SESSION_KEY, $state);
        $request->session()->regenerateToken();

        return redirect()
            ->route('password.initial.create')
            ->with('password_setup_status', 'We sent a 6-digit code to ' . data_get($state, 'phone.masked') . '.');
    }

    public function verifyInitialPasswordOtp(VerifyPasswordResetCodeRequest $request)
    {
        $state = $this->initialPasswordState($request);

        if (! $state) {
            return redirect()
                ->route('login')
                ->withErrors(['member_id' => 'Start from the member login page to set your first password.']);
        }

        $otpRecord = $this->otpRecord((int) data_get($state, 'otp_id'));

        if (! $otpRecord || $this->otpRecordPhone($otpRecord) !== (string) data_get($state, 'phone.e164_digits')) {
            $state = $this->resetInitialPasswordOtpState($state);
            $request->session()->put(self::INITIAL_PASSWORD_SESSION_KEY, $state);

            return redirect()
                ->route('password.initial.create')
                ->withErrors(['phone' => 'The code has expired. Request a new one.']);
        }

        $attempts = (int) data_get($state, 'attempts', 0);

        if ($attempts >= self::MAX_OTP_ATTEMPTS) {
            $this->updateOtpStatus((int) data_get($state, 'otp_id'), 'BLOCKED');
            $state = $this->resetInitialPasswordOtpState($state);
            $request->session()->put(self::INITIAL_PASSWORD_SESSION_KEY, $state);

            return redirect()
                ->route('password.initial.create')
                ->withErrors(['phone' => 'Too many invalid attempts. Request a new code.']);
        }

        if ((int) data_get($otpRecord, 'OTP') !== (int) $request->input('code')) {
            $state['attempts'] = $attempts + 1;
            $request->session()->put(self::INITIAL_PASSWORD_SESSION_KEY, $state);

            if ((int) data_get($state, 'attempts', 0) >= self::MAX_OTP_ATTEMPTS) {
                $this->updateOtpStatus((int) data_get($state, 'otp_id'), 'BLOCKED');
            }

            throw ValidationException::withMessages([
                'code' => 'The OTP is invalid. Please check the SMS and try again.',
            ]);
        }

        $state['verified_at'] = now()->timestamp;
        $state['verified_until'] = now()->addMinutes(self::OTP_TTL_MINUTES)->timestamp;
        $state['attempts'] = 0;

        $this->updateOtpStatus((int) data_get($state, 'otp_id'), 'VERIFIED');
        $request->session()->put(self::INITIAL_PASSWORD_SESSION_KEY, $state);
        $request->session()->regenerateToken();

        return redirect()
            ->route('password.initial.create')
            ->with('password_setup_status', 'OTP confirmed. Set your new password now.');
    }

    public function resendInitialPasswordOtp(Request $request, RobiSmsService $robiSms)
    {
        $state = $this->initialPasswordState($request);

        if (! $state || $this->initialPasswordOtpVerified($state)) {
            return redirect()->route('password.initial.create');
        }

        $phone = data_get($state, 'phone');

        if (! is_array($phone) || ! filled(data_get($phone, 'e164_digits'))) {
            return redirect()
                ->route('password.initial.create')
                ->withErrors(['phone' => 'No registered Bangladesh mobile number was found for this member ID. Contact the club office.']);
        }

        if ((int) data_get($state, 'otp_id') > 0) {
            $this->updateOtpStatus((int) data_get($state, 'otp_id'), 'RESENT');
        }

        try {
            $otpState = $this->dispatchOtp(
                $phone,
                [[
                    'member_id' => data_get($state, 'member_id'),
                    'member_name' => data_get($state, 'member_name', 'Member'),
                ]],
                $robiSms,
                'new'
            );
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'code' => 'We could not resend the code right now. Please try again shortly.',
            ]);
        }

        $state = [
            ...$state,
            ...$otpState,
        ];

        $request->session()->put(self::INITIAL_PASSWORD_SESSION_KEY, $state);
        $request->session()->regenerateToken();

        return redirect()
            ->route('password.initial.create')
            ->with('password_setup_status', 'A fresh OTP has been sent to ' . data_get($state, 'phone.masked') . '.');
    }

    public function storeInitialPassword(InitializePasswordRequest $request)
    {
        $state = $this->initialPasswordState($request);

        if (! $state) {
            return redirect()
                ->route('login')
                ->withErrors(['member_id' => 'Start from the member login page to set your first password.']);
        }

        if (! $this->initialPasswordOtpVerified($state)) {
            return redirect()
                ->route('password.initial.create')
                ->withErrors(['code' => 'Verify the OTP before creating your password.']);
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

        $this->updateOtpStatus((int) data_get($state, 'otp_id'), 'USED');
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

        $redirect = redirect()->route('login');

        if ($request->boolean('inactive')) {
            $redirect->with('session_expired', MemberSession::EXPIRY_MESSAGE);
        }

        return $redirect;
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

        if ($this->hasInitialPasswordOtpExpired($state) && ! $this->initialPasswordOtpVerified($state)) {
            $this->updateOtpStatus((int) data_get($state, 'otp_id'), 'EXPIRED');
            $state = $this->resetInitialPasswordOtpState($state);
            $request->session()->put(self::INITIAL_PASSWORD_SESSION_KEY, $state);
        }

        if ($this->initialPasswordOtpVerified($state) && $this->hasExpired((int) data_get($state, 'verified_until'))) {
            $this->updateOtpStatus((int) data_get($state, 'otp_id'), 'EXPIRED');
            $state = $this->resetInitialPasswordOtpState($state);
            $request->session()->put(self::INITIAL_PASSWORD_SESSION_KEY, $state);
        }

        $state = $this->hydrateInitialPasswordState($request, $state);

        return $state;
    }

    private function clearInitialPasswordState(Request $request): void
    {
        $request->session()->forget(self::INITIAL_PASSWORD_SESSION_KEY);
    }

    private function hydrateInitialPasswordState(Request $request, array $state): array
    {
        if (filled(data_get($state, 'phone.e164_digits'))) {
            return $state;
        }

        $member = MemberAccess::findActiveMember((string) data_get($state, 'member_id'), [
            'c.PrvCusID',
            'c.Title',
            'c.CusName',
            'c.Mobile',
            'c.Phone',
        ]);

        if (! $member) {
            return $state;
        }

        $state['member_name'] = MemberAccess::displayName($member);
        $state['phone'] = MemberAccess::registeredPhone($member);

        $request->session()->put(self::INITIAL_PASSWORD_SESSION_KEY, $state);

        return $state;
    }

    private function initialPasswordOtpPending(array $state): bool
    {
        return (int) data_get($state, 'otp_id', 0) > 0
            && ! $this->initialPasswordOtpVerified($state)
            && ! $this->hasInitialPasswordOtpExpired($state);
    }

    private function initialPasswordOtpVerified(array $state): bool
    {
        return is_numeric(data_get($state, 'verified_at'))
            && ! $this->hasExpired((int) data_get($state, 'verified_until'));
    }

    private function hasInitialPasswordOtpExpired(array $state): bool
    {
        return (int) data_get($state, 'otp_id', 0) > 0
            && $this->hasExpired((int) data_get($state, 'expires_at'));
    }

    private function hasExpired(int $timestamp): bool
    {
        return $timestamp > 0 && $timestamp <= now()->timestamp;
    }

    private function resetInitialPasswordOtpState(array $state): array
    {
        $state['otp_id'] = null;
        $state['sent_at'] = null;
        $state['expires_at'] = null;
        $state['attempts'] = 0;
        $state['verified_at'] = null;
        $state['verified_until'] = null;

        return $state;
    }
}
