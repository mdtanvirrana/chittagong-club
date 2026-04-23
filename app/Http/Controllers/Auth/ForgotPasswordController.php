<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordWithOtpRequest;
use App\Http\Requests\Auth\SendPasswordResetCodeRequest;
use App\Http\Requests\Auth\VerifyPasswordResetCodeRequest;
use App\Services\Auth\RobiSmsService;
use App\Support\BangladeshMobile;
use App\Support\MemberAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Throwable;

class ForgotPasswordController extends Controller
{
    private const SESSION_KEY = 'member_password_reset';
    private const CACHE_PREFIX = 'member_password_reset_otp_';
    private const OTP_TTL_MINUTES = 10;
    private const MAX_OTP_ATTEMPTS = 5;

    public function create(Request $request)
    {
        return $this->guestView('pages.forgot-password', [
            'resetState' => $this->activeState($request),
        ]);
    }

    public function sendCode(SendPasswordResetCodeRequest $request, RobiSmsService $robiSms)
    {
        $phone = BangladeshMobile::normalize((string) $request->input('phone'));

        if (! $phone) {
            throw ValidationException::withMessages([
                'phone' => 'Enter a valid Bangladesh mobile number.',
            ]);
        }

        $accounts = MemberAccess::findAccountsByPhone($phone['candidates']);

        if ($accounts->isEmpty()) {
            throw ValidationException::withMessages([
                'phone' => 'We could not match this mobile number to a member login.',
            ]);
        }

        $otp = (string) random_int(100000, 999999);
        $cacheKey = $this->otpCacheKey($phone['e164_digits']);
        $expiresAt = now()->addMinutes(self::OTP_TTL_MINUTES);

        try {
            $robiSms->sendOtp(
                $phone['e164'],
                "Your password reset code is {$otp}. It expires in 10 minutes."
            );
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'phone' => 'We could not send the code right now. Please try again shortly.',
            ]);
        }

        Cache::put($cacheKey, [
            'otp_hash' => $this->otpHash($otp),
            'attempts' => 0,
        ], $expiresAt);

        $request->session()->put(self::SESSION_KEY, [
            'phone' => $phone,
            'accounts' => $accounts->all(),
            'sent_at' => now()->timestamp,
            'expires_at' => $expiresAt->timestamp,
            'verified_at' => null,
            'verified_until' => null,
        ]);

        $request->session()->regenerateToken();

        return redirect()
            ->route('password.forgot.verify')
            ->with('password_reset_status', 'We sent a 6-digit code to ' . $phone['masked'] . '.');
    }

    public function showVerify(Request $request)
    {
        $state = $this->activeState($request);

        if (! $state) {
            return redirect()->route('password.forgot');
        }

        if ($this->isVerified($state)) {
            return redirect()->route('password.forgot.reset');
        }

        return $this->guestView('pages.forgot-password-otp', [
            'resetState' => $state,
        ]);
    }

    public function verifyCode(VerifyPasswordResetCodeRequest $request)
    {
        $state = $this->activeState($request);

        if (! $state) {
            return redirect()
                ->route('password.forgot')
                ->withErrors(['phone' => 'Start the password reset process again.']);
        }

        $cacheKey = $this->otpCacheKey(data_get($state, 'phone.e164_digits'));
        $otpState = Cache::get($cacheKey);

        if (! is_array($otpState)) {
            $this->clearState($request);

            return redirect()
                ->route('password.forgot')
                ->withErrors(['phone' => 'The code has expired. Request a new one.']);
        }

        $attempts = (int) ($otpState['attempts'] ?? 0);

        if ($attempts >= self::MAX_OTP_ATTEMPTS) {
            Cache::forget($cacheKey);
            $this->clearState($request);

            return redirect()
                ->route('password.forgot')
                ->withErrors(['phone' => 'Too many invalid attempts. Request a new code.']);
        }

        if (! hash_equals((string) ($otpState['otp_hash'] ?? ''), $this->otpHash((string) $request->input('code')))) {
            $this->updateOtpState($cacheKey, $otpState, (int) data_get($state, 'expires_at'));

            throw ValidationException::withMessages([
                'code' => 'The OTP is invalid. Please check the SMS and try again.',
            ]);
        }

        Cache::forget($cacheKey);

        $state['verified_at'] = now()->timestamp;
        $state['verified_until'] = now()->addMinutes(self::OTP_TTL_MINUTES)->timestamp;

        $request->session()->put(self::SESSION_KEY, $state);
        $request->session()->regenerateToken();

        return redirect()
            ->route('password.forgot.reset')
            ->with('password_reset_status', 'OTP confirmed. Set a new password now.');
    }

    public function resendCode(Request $request, RobiSmsService $robiSms)
    {
        $state = $this->activeState($request);

        if (! $state || $this->isVerified($state)) {
            return redirect()->route('password.forgot');
        }

        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(self::OTP_TTL_MINUTES);

        try {
            $robiSms->sendOtp(
                (string) data_get($state, 'phone.e164'),
                "Your password reset code is {$otp}. It expires in 10 minutes."
            );
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'code' => 'We could not resend the code right now. Please try again shortly.',
            ]);
        }

        Cache::put($this->otpCacheKey(data_get($state, 'phone.e164_digits')), [
            'otp_hash' => $this->otpHash($otp),
            'attempts' => 0,
        ], $expiresAt);

        $state['sent_at'] = now()->timestamp;
        $state['expires_at'] = $expiresAt->timestamp;

        $request->session()->put(self::SESSION_KEY, $state);
        $request->session()->regenerateToken();

        return redirect()
            ->route('password.forgot.verify')
            ->with('password_reset_status', 'A fresh OTP has been sent to ' . data_get($state, 'phone.masked') . '.');
    }

    public function showReset(Request $request)
    {
        $state = $this->activeState($request);

        if (! $state) {
            return redirect()->route('password.forgot');
        }

        if (! $this->isVerified($state)) {
            return redirect()->route('password.forgot.verify');
        }

        return $this->guestView('pages.forgot-password-reset', [
            'resetState' => $state,
            'requiresMemberSelection' => count(data_get($state, 'accounts', [])) > 1,
        ]);
    }

    public function updatePassword(ResetPasswordWithOtpRequest $request)
    {
        $state = $this->activeState($request);

        if (! $state || ! $this->isVerified($state)) {
            return redirect()
                ->route('password.forgot')
                ->withErrors(['phone' => 'Start the password reset process again.']);
        }

        $accounts = collect(data_get($state, 'accounts', []));
        $memberId = $accounts->count() === 1
            ? trim((string) data_get($accounts->first(), 'member_id'))
            : trim((string) $request->input('member_id'));

        if ($accounts->count() > 1 && $memberId === '') {
            throw ValidationException::withMessages([
                'member_id' => 'Choose the correct member ID for this mobile number.',
            ]);
        }

        if (! $accounts->contains(fn (array $account): bool => trim((string) data_get($account, 'member_id')) === $memberId)) {
            throw ValidationException::withMessages([
                'member_id' => 'Choose a valid member ID from the list.',
            ]);
        }

        $updated = MemberAccess::changePassword(
            $memberId,
            (string) $request->input('password'),
            'forget'
        );

        if (! $updated) {
            throw ValidationException::withMessages([
                'password' => 'Unable to update the password right now. Please try again.',
            ]);
        }

        $this->clearState($request);
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('password_reset_status', 'Password updated successfully. Sign in with your new password.');
    }

    private function guestView(string $view, array $data = [])
    {
        return response()
            ->view($view, $data)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    private function activeState(Request $request): ?array
    {
        $state = $request->session()->get(self::SESSION_KEY);

        if (! is_array($state)) {
            return null;
        }

        if ($this->hasExpired((int) data_get($state, 'expires_at')) && ! $this->isVerified($state)) {
            $this->clearState($request);

            return null;
        }

        if ($this->isVerified($state) && $this->hasExpired((int) data_get($state, 'verified_until'))) {
            $this->clearState($request);

            return null;
        }

        return $state;
    }

    private function isVerified(array $state): bool
    {
        return is_numeric(data_get($state, 'verified_at')) && ! $this->hasExpired((int) data_get($state, 'verified_until'));
    }

    private function hasExpired(int $timestamp): bool
    {
        return $timestamp > 0 && $timestamp <= now()->timestamp;
    }

    private function otpCacheKey(?string $phoneDigits): string
    {
        return self::CACHE_PREFIX . trim((string) $phoneDigits);
    }

    private function otpHash(string $otp): string
    {
        return hash_hmac('sha256', $otp, (string) config('app.key'));
    }

    private function updateOtpState(string $cacheKey, array $otpState, int $expiresAt): void
    {
        $secondsRemaining = max(1, $expiresAt - now()->timestamp);
        $otpState['attempts'] = ((int) ($otpState['attempts'] ?? 0)) + 1;

        if ((int) $otpState['attempts'] >= self::MAX_OTP_ATTEMPTS) {
            Cache::forget($cacheKey);

            return;
        }

        Cache::put($cacheKey, $otpState, now()->addSeconds($secondsRemaining));
    }

    private function clearState(Request $request): void
    {
        $phoneDigits = data_get($request->session()->get(self::SESSION_KEY), 'phone.e164_digits');

        if (filled($phoneDigits)) {
            Cache::forget($this->otpCacheKey($phoneDigits));
        }

        $request->session()->forget(self::SESSION_KEY);
    }
}
