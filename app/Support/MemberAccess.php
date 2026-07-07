<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MemberAccess
{
    public static function activeMemberQuery(string $customerAlias = 'c', string $categoryAlias = 'cc')
    {
        return DB::table("CustomerMst as {$customerAlias}")
            ->join("CusCardCatagory as {$categoryAlias}", "{$customerAlias}.Cardid", '=', "{$categoryAlias}.CardID")
            ->where("{$categoryAlias}.GM", 'M')
            ->where("{$customerAlias}.MemExpTypeID", 100);
    }

    public static function findActiveMember(string $memberId, array $columns = ['c.*']): ?object
    {
        $memberId = trim((string) $memberId);

        if ($memberId === '') {
            return null;
        }

        return static::activeMemberQuery()
            ->where('c.PrvCusID', $memberId)
            ->select($columns)
            ->first();
    }

    public static function activeMemberExists(string $memberId): bool
    {
        $memberId = trim((string) $memberId);

        if ($memberId === '') {
            return false;
        }

        return static::activeMemberQuery()
            ->where('c.PrvCusID', $memberId)
            ->exists();
    }

    public static function credentialsMatch(string $memberId, string $password): bool
    {
        $credentials = static::memberCredentials($memberId);

        if (static::isAdminCredentials($credentials)) {
            return false;
        }

        $storedPassword = static::storedPasswordFromCredentials($credentials);

        if ($storedPassword === null || $password === '') {
            return false;
        }

        return hash_equals(static::hashPassword($password), strtolower($storedPassword))
            || hash_equals($storedPassword, $password);
    }

    public static function passwordSetupRequired(string $memberId): bool
    {
        $credentials = static::memberCredentials($memberId);

        return ! static::isAdminCredentials($credentials)
            && static::storedPasswordFromCredentials($credentials) === null;
    }

    public static function changePassword(string $memberId, string $password, string $note): bool
    {
        $memberId = trim((string) $memberId);

        if ($memberId === '' || $password === '') {
            return false;
        }

        if (static::isAdminCredentials(static::memberCredentials($memberId))) {
            return false;
        }

        $timestamp = now();
        $timeText = $timestamp->format('H:i:s');
        $hashedPassword = static::hashPassword($password);

        $passwordHistorySaved = DB::table('Users_App_Pass')->insert([
            'PrvcusID' => $memberId,
            'EDate' => $timestamp,
            'ETime' => $timeText,
            'NewPass' => static::truncateText($hashedPassword, 100, '0'),
            'ConPass' => static::truncateText($hashedPassword, 100, '0'),
            'Note' => static::truncateText($note, 100, 'Password updated'),
        ]);

        $credentialsSaved = DB::table('Users_App')->updateOrInsert(
            ['PrvcusID' => $memberId],
            [
                'Password' => static::truncateText($hashedPassword, 40, '0'),
                'is_admin' => 0,
                'LastUpdateDate' => $timestamp,
                'LastUpdateTime' => $timeText,
            ]
        );

        return $passwordHistorySaved && $credentialsSaved;
    }

    public static function recordActivity(string $memberId, string $status, ?string $ipAddress = null): bool
    {
        $memberId = trim((string) $memberId);

        if ($memberId === '') {
            return false;
        }

        $timestamp = now();

        return DB::table('UsersLog')->insert([
            'PrvcusID' => $memberId,
            'EDate' => $timestamp,
            'ETime' => $timestamp->format('H:i:s'),
            'eIP' => static::truncateText($ipAddress, 1000, '0'),
            'Status' => static::truncateText($status, 100, '0'),
        ]);
    }

    public static function findAccountsByPhone(array $candidateDigits): Collection
    {
        $mobileExpression = DB::raw(static::digitsOnlyExpression('c.Mobile'));
        $phoneExpression = DB::raw(static::digitsOnlyExpression('c.Phone'));

        return static::activeMemberQuery()
            ->leftJoin('Users_App as ua', 'c.PrvCusID', '=', 'ua.PrvcusID')
            ->select([
                'c.PrvCusID as member_id',
                'c.CusName as member_name',
            ])
            ->where(function ($query): void {
                $query->whereNull('ua.is_admin')
                    ->orWhere('ua.is_admin', 0);
            })
            ->where(function ($query) use ($candidateDigits, $mobileExpression, $phoneExpression): void {
                $query->whereIn($mobileExpression, $candidateDigits)
                    ->orWhereIn($phoneExpression, $candidateDigits);
            })
            ->get()
            ->map(function (object $member): array {
                $memberId = trim((string) ($member->member_id ?? ''));
                $name = trim((string) ($member->member_name ?? 'Member'));

                return [
                    'member_id' => $memberId,
                    'member_name' => $name !== '' ? $name : 'Member',
                    'label' => $memberId . ' • ' . ($name !== '' ? $name : 'Member'),
                ];
            })
            ->filter(fn (array $account): bool => $account['member_id'] !== '')
            ->unique('member_id')
            ->values();
    }

    public static function displayName(?object $member): string
    {
        if (! $member) {
            return 'Member';
        }

        $title = trim((string) data_get($member, 'Title'));
        $name = trim((string) data_get($member, 'CusName'));
        $displayName = trim($title . ' ' . $name);

        return $displayName !== '' ? $displayName : 'Member';
    }

    public static function registeredPhone(?object $member): ?array
    {
        if (! $member) {
            return null;
        }

        foreach ([
            trim((string) data_get($member, 'Mobile')),
            trim((string) data_get($member, 'Phone')),
        ] as $candidate) {
            $phone = BangladeshMobile::normalize($candidate);

            if ($phone) {
                return $phone;
            }
        }

        return null;
    }

    private static function digitsOnlyExpression(string $column): string
    {
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(ISNULL({$column}, ''), ' ', ''), '+', ''), '-', ''), '(', ''), ')', ''), '.', ''), '/', ''), CHAR(9), '')";
    }

    private static function memberCredentials(string $memberId): ?object
    {
        $memberId = trim((string) $memberId);

        if ($memberId === '') {
            return null;
        }

        return DB::table('Users_App')
            ->where('PrvcusID', $memberId)
            ->first(['Password', 'is_admin']);
    }

    private static function isAdminCredentials(?object $credentials): bool
    {
        return (int) data_get($credentials, 'is_admin', 0) === 1;
    }

    private static function storedPasswordFromCredentials(?object $credentials): ?string
    {
        if (! $credentials) {
            return null;
        }

        $password = data_get($credentials, 'Password');

        $password = trim((string) $password);

        return $password !== '' && $password !== '0' ? $password : null;
    }

    private static function hashPassword(string $password): string
    {
        return md5($password);
    }

    private static function truncateText(?string $value, int $length, string $fallback): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $fallback;
        }

        return mb_substr($value, 0, $length);
    }
}
