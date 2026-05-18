<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Auth\Concerns\HandlesMemberOtp;
use App\Http\Controllers\Controller;
use App\Models\MemberApiUser;
use App\Services\Auth\RobiSmsService;
use App\Support\BangladeshMobile;
use App\Support\MemberAccess;
use App\Support\PortalCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    use HandlesMemberOtp;

    private const OTP_TTL_MINUTES = 5;
    private const MAX_OTP_ATTEMPTS = 5;
    private const RESET_STATE_TTL_MINUTES = 10;

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'member_id' => ['required', 'string', 'max:40'],
            'password' => ['required', 'string', 'max:40'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $memberId = trim((string) $validated['member_id']);
        $password = (string) $validated['password'];

        $member = MemberAccess::findActiveMember($memberId, [
            'c.PrvCusID',
            'c.Title',
            'c.CusName',
            'c.Mobile',
            'c.Phone',
        ]);

        if (! $member || ! MemberAccess::credentialsMatch($memberId, $password)) {
            throw ValidationException::withMessages([
                'member_id' => 'Invalid Membership ID or password.',
            ]);
        }

        $user = MemberApiUser::query()->whereKey((string) $member->PrvCusID)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'member_id' => 'Member app credentials were not found. Please contact the club office.',
            ]);
        }

        $deviceName = trim((string) ($validated['device_name'] ?? ''));
        $deviceName = $deviceName !== '' ? $deviceName : 'mobile-' . Str::lower(Str::random(8));
        $expiresAt = now()->addDays(max(1, (int) config('sanctum.member_mobile_expiration_days', 30)));
        $token = $user->createToken($deviceName, ['member'], $expiresAt);

        MemberAccess::recordActivity($member->PrvCusID, 'API Login', $request->ip());

        return response()->json([
            'message' => 'Signed in.',
            'token_type' => 'Bearer',
            'access_token' => $token->plainTextToken,
            'expires_at' => $expiresAt->toIso8601String(),
            'member' => $this->memberPayload($member),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $member = $this->activeMemberForRequest($request);

        if (! $member) {
            return response()->json(['message' => 'Member not found.'], 404);
        }

        return response()->json([
            'member' => $this->memberPayload($member),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $memberId = trim((string) data_get($request->user(), 'member_id'));

        if ($memberId !== '') {
            MemberAccess::recordActivity($memberId, 'API Logout', $request->ip());
        }

        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Signed out.',
        ]);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()?->tokens()->delete();

        return response()->json([
            'message' => 'Signed out from all devices.',
        ]);
    }

    public function sendForgotPasswordCode(Request $request, RobiSmsService $robiSms): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
        ], [
            'phone.required' => 'Enter your registered mobile number.',
        ]);

        $phone = BangladeshMobile::normalize((string) $validated['phone']);

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

        try {
            $otpState = $this->dispatchOtp($phone, $accounts->all(), $robiSms, 'forget');
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'phone' => 'We could not send the code right now. Please try again shortly.',
            ]);
        }

        $token = $this->newResetToken();
        $state = [
            'purpose' => 'forgot',
            'phone' => $phone,
            'accounts' => $accounts->all(),
            ...$otpState,
        ];

        $this->putResetState($token, $state);

        return response()->json([
            'message' => 'We sent a 6-digit code to '.$phone['masked'].'.',
            'reset_token' => $token,
            'masked_phone' => $phone['masked'],
            'expires_at' => $otpState['expires_at'],
        ]);
    }

    public function verifyForgotPasswordCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reset_token' => ['required', 'string', 'max:120'],
            'code' => ['required', 'digits:6'],
        ]);

        [$token, $state] = $this->resetStateForToken((string) $validated['reset_token'], 'forgot');
        $this->assertOtpCode($token, $state, (string) $validated['code']);

        $state['verified_at'] = now()->timestamp;
        $state['verified_until'] = now()->addMinutes(self::OTP_TTL_MINUTES)->timestamp;
        $state['attempts'] = 0;
        $this->updateOtpStatus((int) data_get($state, 'otp_id'), 'VERIFIED');
        $this->putResetState($token, $state);

        return response()->json([
            'message' => 'OTP confirmed. Set a new password now.',
            'reset_token' => $token,
            'accounts' => collect(data_get($state, 'accounts', []))
                ->map(fn (array $account): array => [
                    'member_id' => (string) data_get($account, 'member_id'),
                    'member_name' => (string) data_get($account, 'member_name', 'Member'),
                ])
                ->values(),
        ]);
    }

    public function resetForgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reset_token' => ['required', 'string', 'max:120'],
            'member_id' => ['nullable', 'string', 'max:40'],
            'password' => ['required', 'string', 'min:6', 'max:40', 'confirmed'],
        ], [
            'password.required' => 'Enter a new password.',
            'password.min' => 'The new password must be at least 6 characters.',
            'password.confirmed' => 'Confirm the new password.',
        ]);

        [$token, $state] = $this->resetStateForToken((string) $validated['reset_token'], 'forgot');
        $this->assertVerifiedState($state);

        $accounts = collect(data_get($state, 'accounts', []));
        $memberId = $accounts->count() === 1
            ? trim((string) data_get($accounts->first(), 'member_id'))
            : trim((string) ($validated['member_id'] ?? ''));

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

        if (! MemberAccess::changePassword($memberId, (string) $validated['password'], 'forget')) {
            throw ValidationException::withMessages([
                'password' => 'Unable to update the password right now. Please try again.',
            ]);
        }

        $this->updateOtpStatus((int) data_get($state, 'otp_id'), 'USED');
        Cache::forget($this->resetCacheKey($token));

        return response()->json([
            'message' => 'Password updated successfully. Sign in with your new password.',
        ]);
    }

    public function sendInitialPasswordCode(Request $request, RobiSmsService $robiSms): JsonResponse
    {
        $validated = $request->validate([
            'member_id' => ['required', 'string', 'max:40'],
        ], [
            'member_id.required' => 'Enter your membership ID.',
        ]);

        $memberId = trim((string) $validated['member_id']);
        $member = MemberAccess::findActiveMember($memberId, [
            'c.PrvCusID',
            'c.Title',
            'c.CusName',
            'c.Mobile',
            'c.Phone',
        ]);

        if (! $member) {
            throw ValidationException::withMessages([
                'member_id' => 'Member not found.',
            ]);
        }

        if (! MemberAccess::passwordSetupRequired($memberId)) {
            throw ValidationException::withMessages([
                'member_id' => 'Password already exists for this member ID. Sign in or use Forgot Password.',
            ]);
        }

        $phone = MemberAccess::registeredPhone($member);

        if (! is_array($phone) || ! filled(data_get($phone, 'e164_digits'))) {
            throw ValidationException::withMessages([
                'member_id' => 'No registered Bangladesh mobile number was found for this member ID. Contact the club office.',
            ]);
        }

        $account = [
            'member_id' => $memberId,
            'member_name' => MemberAccess::displayName($member),
        ];

        try {
            $otpState = $this->dispatchOtp($phone, [$account], $robiSms, 'new');
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'member_id' => 'We could not send the code right now. Please try again shortly.',
            ]);
        }

        $token = $this->newResetToken();
        $state = [
            'purpose' => 'initial',
            'phone' => $phone,
            'accounts' => [$account],
            ...$otpState,
        ];

        $this->putResetState($token, $state);

        return response()->json([
            'message' => 'We sent a 6-digit code to '.data_get($phone, 'masked').'.',
            'reset_token' => $token,
            'masked_phone' => data_get($phone, 'masked'),
            'member_id' => $memberId,
            'member_name' => $account['member_name'],
            'expires_at' => $otpState['expires_at'],
        ]);
    }

    public function verifyInitialPasswordCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reset_token' => ['required', 'string', 'max:120'],
            'code' => ['required', 'digits:6'],
        ]);

        [$token, $state] = $this->resetStateForToken((string) $validated['reset_token'], 'initial');
        $this->assertOtpCode($token, $state, (string) $validated['code']);

        $state['verified_at'] = now()->timestamp;
        $state['verified_until'] = now()->addMinutes(self::OTP_TTL_MINUTES)->timestamp;
        $state['attempts'] = 0;
        $this->updateOtpStatus((int) data_get($state, 'otp_id'), 'VERIFIED');
        $this->putResetState($token, $state);

        return response()->json([
            'message' => 'OTP confirmed. Set your new password now.',
            'reset_token' => $token,
        ]);
    }

    public function storeInitialPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reset_token' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string', 'min:6', 'max:40', 'confirmed'],
        ], [
            'password.required' => 'Enter a new password.',
            'password.min' => 'The new password must be at least 6 characters.',
            'password.confirmed' => 'Confirm the new password.',
        ]);

        [$token, $state] = $this->resetStateForToken((string) $validated['reset_token'], 'initial');
        $this->assertVerifiedState($state);

        $memberId = trim((string) data_get($state, 'accounts.0.member_id'));

        if (! MemberAccess::activeMemberExists($memberId)) {
            Cache::forget($this->resetCacheKey($token));

            throw ValidationException::withMessages([
                'member_id' => 'Member not found.',
            ]);
        }

        if (! MemberAccess::passwordSetupRequired($memberId)) {
            Cache::forget($this->resetCacheKey($token));

            throw ValidationException::withMessages([
                'member_id' => 'Password already exists. Sign in with your current password.',
            ]);
        }

        if (! MemberAccess::changePassword($memberId, (string) $validated['password'], 'new')) {
            throw ValidationException::withMessages([
                'password' => 'Unable to save the password right now. Please try again.',
            ]);
        }

        $this->updateOtpStatus((int) data_get($state, 'otp_id'), 'USED');
        Cache::forget($this->resetCacheKey($token));

        return response()->json([
            'message' => 'Password created successfully. Sign in with your new password.',
        ]);
    }

    private function activeMemberForRequest(Request $request): ?object
    {
        $memberId = trim((string) data_get($request->user(), 'member_id'));

        if ($memberId === '') {
            return null;
        }

        return MemberAccess::findActiveMember($memberId, [
            'c.PrvCusID',
            'c.Title',
            'c.CusName',
            'c.Mobile',
            'c.Phone',
        ]);
    }

    private function memberPayload(object $member): array
    {
        $memberId = (string) $member->PrvCusID;

        return [
            'id' => $memberId,
            'name' => MemberAccess::displayName($member),
            'phone' => MemberAccess::registeredPhone($member),
            'has_photo' => PortalCache::hasMemberPhoto($memberId),
            'photo_url' => PortalCache::memberPhotoUrl($memberId),
        ];
    }

    private function newResetToken(): string
    {
        return Str::random(64);
    }

    private function resetCacheKey(string $token): string
    {
        return 'api_member_password_state:'.hash('sha256', $token);
    }

    private function putResetState(string $token, array $state): void
    {
        Cache::put($this->resetCacheKey($token), $state, now()->addMinutes(self::RESET_STATE_TTL_MINUTES));
    }

    private function resetStateForToken(string $token, string $purpose): array
    {
        $state = Cache::get($this->resetCacheKey($token));

        if (! is_array($state) || data_get($state, 'purpose') !== $purpose) {
            throw ValidationException::withMessages([
                'reset_token' => 'Start the password process again.',
            ]);
        }

        if ($this->stateHasExpired($state)) {
            $this->updateOtpStatus((int) data_get($state, 'otp_id'), 'EXPIRED');
            Cache::forget($this->resetCacheKey($token));

            throw ValidationException::withMessages([
                'code' => 'The code has expired. Request a new one.',
            ]);
        }

        return [$token, $state];
    }

    private function assertOtpCode(string $token, array $state, string $code): void
    {
        $otpRecord = $this->otpRecord((int) data_get($state, 'otp_id'));

        if (! $otpRecord || $this->otpRecordPhone($otpRecord) !== (string) data_get($state, 'phone.e164_digits')) {
            $this->updateOtpStatus((int) data_get($state, 'otp_id'), 'EXPIRED');
            Cache::forget($this->resetCacheKey($token));

            throw ValidationException::withMessages([
                'code' => 'The code has expired. Request a new one.',
            ]);
        }

        $attempts = (int) data_get($state, 'attempts', 0);

        if ($attempts >= self::MAX_OTP_ATTEMPTS) {
            $this->updateOtpStatus((int) data_get($state, 'otp_id'), 'BLOCKED');
            Cache::forget($this->resetCacheKey($token));

            throw ValidationException::withMessages([
                'code' => 'Too many invalid attempts. Request a new code.',
            ]);
        }

        if ((int) data_get($otpRecord, 'OTP') !== (int) $code) {
            $state['attempts'] = $attempts + 1;

            if ((int) data_get($state, 'attempts') >= self::MAX_OTP_ATTEMPTS) {
                $this->updateOtpStatus((int) data_get($state, 'otp_id'), 'BLOCKED');
            }

            $this->putResetState($token, $state);

            throw ValidationException::withMessages([
                'code' => 'The OTP is invalid. Please check the SMS and try again.',
            ]);
        }
    }

    private function assertVerifiedState(array $state): void
    {
        if (! is_numeric(data_get($state, 'verified_at')) || $this->hasTimestampExpired((int) data_get($state, 'verified_until'))) {
            throw ValidationException::withMessages([
                'code' => 'Verify the OTP before creating your password.',
            ]);
        }
    }

    private function stateHasExpired(array $state): bool
    {
        if (is_numeric(data_get($state, 'verified_at'))) {
            return $this->hasTimestampExpired((int) data_get($state, 'verified_until'));
        }

        return $this->hasTimestampExpired((int) data_get($state, 'expires_at'));
    }

    private function hasTimestampExpired(int $timestamp): bool
    {
        return $timestamp > 0 && $timestamp <= now()->timestamp;
    }
}
