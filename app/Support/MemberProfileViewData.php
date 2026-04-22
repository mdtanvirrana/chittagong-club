<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MemberProfileViewData
{
    public static function forCurrentMember(): array
    {
        $memberId = trim((string) session('member.id'));

        abort_if($memberId === '', 404, 'Member not found.');

        return static::forMemberId($memberId);
    }

    public static function forMemberId(string|int $memberId): array
    {
        $memberId = trim((string) $memberId);

        abort_if($memberId === '', 404, 'Member not found.');

        $member = PortalCache::remember("member_profile_view_{$memberId}_v1", now()->addMinutes(10), function () use ($memberId) {
            return DB::table('CustomerMst as c')
                ->leftJoin('List_MemExpType as mt', 'c.MemExpTypeID', '=', 'mt.MemExpTypeID')
                ->leftJoin('CusCardCatagory as cc', 'c.Cardid', '=', 'cc.Cardid')
                ->where('c.PrvCusID', $memberId)
                ->select([
                    'c.PrvCusID',
                    'c.Title',
                    'c.CusName',
                    'c.BloodGroup',
                    'c.Phone',
                    'c.Mobile',
                    'c.Email',
                    'c.Address',
                    'c.City',
                    'c.Profession',
                    'c.Sex',
                    'c.BirthDt',
                    'c.DOE',
                    'c.ExpDt',
                    'c.MaritalStatus',
                    'c.MarriageDT',
                    'c.SpouseName',
                    'c.SpoBlood',
                    'c.SpoMobile',
                    'c.NoChild',
                    'c.Child1',
                    'c.Child2',
                    'c.Child3',
                    'c.Child4',
                    'c.DTChild1',
                    'c.DTChild2',
                    'c.DTChild3',
                    'c.DTChild4',
                    'c.Child1Mobile',
                    'c.Child2Mobile',
                    'c.Child3Mobile',
                    'c.Child4Mobile',
                    'c.child1Sex',
                    'c.child2Sex',
                    'c.child3Sex',
                    'c.child4Sex',
                    'c.child1Blood',
                    'c.child2Blood',
                    'c.child3Blood',
                    'c.child4Blood',
                    'c.Child1Email',
                    'c.Child2Email',
                    'c.Child3Email',
                    'c.Child4Email',
                    'c.MoreChild',
                    'c.FatherName',
                    'c.MotherName',
                    'c.Religion',
                    'c.Nationality',
                    'c.NID',
                    'c.PassportNo',
                    'c.CreditBal',
                    'mt.MemExpTypeName',
                    'cc.Remarks as MemberCategory',
                ])
                ->first();
        });

        if (! $member) {
            abort(404, 'Member not found.');
        }

        $words = preg_split('/\s+/', trim((string) $member->CusName)) ?: [];
        $words = array_values(array_filter($words));
        $fullName = trim(($member->Title ? $member->Title . ' ' : '') . $member->CusName);
        $initials = collect(array_slice($words, 0, 2))
            ->map(fn (string $word) => strtoupper(substr($word, 0, 1)))
            ->join('');

        $birthDate = $member->BirthDt ? Carbon::parse($member->BirthDt)->format('M d, Y') : '—';
        $joinDate = $member->DOE ? Carbon::parse($member->DOE)->format('M d, Y') : '—';
        $weddingDt = $member->MarriageDT ? Carbon::parse($member->MarriageDT)->format('M d, Y') : '—';
        $age = $member->BirthDt ? Carbon::parse($member->BirthDt)->age . ' yrs' : '—';
        $isMarried = in_array(strtolower((string) ($member->MaritalStatus ?? '')), ['m', 'married'], true);
        $callNumber = $member->Mobile ?: $member->Phone;

        return [
            'member' => $member,
            'fullName' => $fullName,
            'initials' => $initials,
            'birthDate' => $birthDate,
            'joinDate' => $joinDate,
            'weddingDt' => $weddingDt,
            'age' => $age,
            'isMarried' => $isMarried,
            'statusColor' => match (strtolower((string) ($member->MemExpTypeName ?? ''))) {
                'active' => 'bg-primary/10 text-primary',
                'expired' => 'bg-slate-100 text-slate-500',
                default => 'bg-primary/5 text-slate-600',
            },
            'callHref' => static::buildPhoneHref($callNumber, 'tel'),
            'smsHref' => static::buildPhoneHref($callNumber, 'sms'),
            'emailHref' => static::buildEmailHref($member->Email ?? null),
            'hasProfilePhoto' => PortalCache::hasMemberPhoto($member->PrvCusID),
            'profilePhotoUrl' => PortalCache::memberPhotoUrl($member->PrvCusID),
            'children' => static::buildChildren($member),
            'childrenCount' => (int) (static::normalizeZeroValue($member->NoChild ?? null) ?? 0),
            'hasMoreChildren' => static::toBool($member->MoreChild ?? false),
        ];
    }

    private static function buildPhoneHref(?string $number, string $scheme): ?string
    {
        $sanitized = static::sanitizePhone($number);

        return $sanitized ? $scheme . ':' . $sanitized : null;
    }

    private static function buildEmailHref(?string $email): ?string
    {
        $email = trim((string) $email);

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? 'mailto:' . $email : null;
    }

    private static function sanitizePhone(?string $number): ?string
    {
        $number = trim((string) $number);

        if ($number === '') {
            return null;
        }

        $hasLeadingPlus = str_starts_with($number, '+');
        $digits = preg_replace('/\D+/', '', $number);

        if ($digits === '') {
            return null;
        }

        return $hasLeadingPlus ? '+' . $digits : $digits;
    }

    private static function buildChildren(object $member): array
    {
        $children = [];

        for ($i = 1; $i <= 4; $i++) {
            $name = static::normalizeZeroValue($member->{'Child' . $i} ?? null);

            if (! $name) {
                continue;
            }

            $children[] = array_filter([
                'slot' => $i,
                'name' => $name,
                'dob' => static::formatChildDate($member->{'DTChild' . $i} ?? null),
                'sex' => static::normalizeChildSex($member->{'child' . $i . 'Sex'} ?? null),
                'blood' => static::normalizeChildBlood($member->{'child' . $i . 'Blood'} ?? null),
                'mobile' => static::normalizeZeroValue($member->{'Child' . $i . 'Mobile'} ?? null),
                'email' => static::normalizeEmail($member->{'Child' . $i . 'Email'} ?? null),
            ], fn ($value) => $value !== null && $value !== '');
        }

        return $children;
    }

    private static function formatChildDate(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            $date = Carbon::parse($value);

            if ($date->year <= 1900) {
                return null;
            }

            return $date->format('M d, Y');
        } catch (\Throwable) {
            return null;
        }
    }

    private static function normalizeChildSex(?string $value): ?string
    {
        return match (strtoupper((string) static::normalizeZeroValue($value))) {
            'M' => 'Male',
            'F' => 'Female',
            default => null,
        };
    }

    private static function normalizeChildBlood(?string $value): ?string
    {
        $value = static::normalizeZeroValue($value);

        if (! $value) {
            return null;
        }

        return in_array(strtoupper($value), ['NO', 'N', 'NA', 'N/A'], true) ? null : $value;
    }

    private static function normalizeEmail(?string $value): ?string
    {
        $value = static::normalizeZeroValue($value);

        return $value && filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
    }

    private static function normalizeZeroValue(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '0') {
            return null;
        }

        return $value;
    }

    private static function toBool(mixed $value): bool
    {
        return in_array((string) $value, ['1', 'true', 'True'], true) || $value === true || $value === 1;
    }
}
