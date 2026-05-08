<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MemberSession
{
    public const KEY = 'member';

    public const EXPIRY_MESSAGE = 'Your session expired after 5 minutes of inactivity. Please sign in again.';

    public static function build(string $memberId, ?string $memberName = null): array
    {
        $issuedAt = now();

        return [
            'id' => $memberId,
            'name' => filled($memberName) ? $memberName : 'Member',
            'issued_at' => $issuedAt->timestamp,
            'expires_at' => $issuedAt->copy()->addMinutes(static::lifetimeMinutes())->timestamp,
        ];
    }

    public static function lifetimeMinutes(): int
    {
        return max(1, (int) config('auth.member_session_lifetime', 5));
    }

    public static function isExpired(?array $member): bool
    {
        $expiresAt = data_get($member, 'expires_at');

        return is_numeric($expiresAt) && (int) $expiresAt <= now()->timestamp;
    }

    public static function needsExpiryRefresh(?array $member): bool
    {
        return filled(data_get($member, 'id')) && ! is_numeric(data_get($member, 'expires_at'));
    }

    public static function touch(Request $request): void
    {
        $member = Session::get(static::KEY, []);

        if (! filled(data_get($member, 'id'))) {
            return;
        }

        $issuedAt = is_numeric(data_get($member, 'issued_at'))
            ? (int) data_get($member, 'issued_at')
            : now()->timestamp;

        $member['issued_at'] = $issuedAt;
        $member['expires_at'] = now()->addMinutes(static::lifetimeMinutes())->timestamp;

        $request->session()->put(static::KEY, $member);
    }

    public static function refreshExpiry(Request $request): void
    {
        static::touch($request);
    }

    public static function logout(Request $request): void
    {
        $memberId = trim((string) data_get(Session::get(static::KEY), 'id'));

        if ($memberId !== '') {
            PortalCache::clearMemberRelatedCaches($memberId);
        }

        Session::forget(static::KEY);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
