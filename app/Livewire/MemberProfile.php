<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Support\PortalCache;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

#[Layout('layouts.userpanel')]
#[Title('My Profile — Chittagong Club Ltd.')]
class MemberProfile extends Component
{
    // ── Public properties ─────────────────────────────────
    public object $member;

    public string $fullName = '';
    public string $initials = '';
    public string $joinDate = '—';
    public string $birthDate = '—';
    public string $age = '—';
    public string $weddingDt = '—';
    public bool   $isMarried   = false;
    public string $statusColor = '';
    public ?string $callHref = null;
    public ?string $smsHref = null;
    public ?string $emailHref = null;
    public bool $hasProfilePhoto = false;
    public ?string $profilePhotoUrl = null;
    public array $children = [];
    public int $childrenCount = 0;
    public bool $hasMoreChildren = false;

    // ── Lifecycle ─────────────────────────────────────────
    public function mount(): void
    {
        $memberId = session('member')['id'];

        $member = PortalCache::remember("member_profile_{$memberId}_v1", now()->addMinutes(10), function () use ($memberId) {
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

        $this->member = $member;
        $this->computeDisplayValues();
    }

    // ── Private helpers ───────────────────────────────────
    private function computeDisplayValues(): void
    {
        $m = $this->member;

        $this->fullName = trim(($m->Title ? $m->Title . ' ' : '') . $m->CusName);

        $this->initials = collect(explode(' ', $m->CusName))
            ->map(fn($w) => strtoupper($w[0] ?? ''))
            ->take(2)
            ->join('');

        $this->joinDate  = $m->DOE        ? Carbon::parse($m->DOE)->format('M d, Y')        : '—';
        $this->birthDate = $m->BirthDt    ? Carbon::parse($m->BirthDt)->format('M d, Y')    : '—';
        $this->age       = $m->BirthDt    ? Carbon::parse($m->BirthDt)->age . ' yrs'        : '—';
        $this->weddingDt = $m->MarriageDT ? Carbon::parse($m->MarriageDT)->format('M d, Y') : '—';

        $this->isMarried = in_array(
            strtolower($m->MaritalStatus ?? ''),
            ['m', 'married']
        );

        $this->statusColor = match (strtolower($m->MemExpTypeName ?? '')) {
            'active'  => 'bg-primary/10 text-primary',
            'expired' => 'bg-slate-100 text-slate-500',
            default   => 'bg-primary/5 text-slate-600',
        };

        $callNumber = $m->Mobile ?: $m->Phone;

        $this->callHref = $this->buildPhoneHref($callNumber, 'tel');
        $this->smsHref = $this->buildPhoneHref($callNumber, 'sms');
        $this->emailHref = $this->buildEmailHref($m->Email ?? null);

        $this->hasProfilePhoto = PortalCache::hasMemberPhoto($m->PrvCusID);
        $this->profilePhotoUrl = PortalCache::memberPhotoUrl($m->PrvCusID);

        $this->childrenCount = (int) $this->normalizeZeroValue($m->NoChild);
        $this->hasMoreChildren = $this->toBool($m->MoreChild ?? false);
        $this->children = $this->buildChildren($m);
    }

    private function buildPhoneHref(?string $number, string $scheme): ?string
    {
        $sanitized = $this->sanitizePhone($number);

        return $sanitized ? $scheme . ':' . $sanitized : null;
    }

    private function buildEmailHref(?string $email): ?string
    {
        $email = trim((string) $email);

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? 'mailto:' . $email : null;
    }

    private function sanitizePhone(?string $number): ?string
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

    private function buildChildren(object $member): array
    {
        $children = [];

        for ($i = 1; $i <= 4; $i++) {
            $name = $this->normalizeZeroValue($member->{'Child' . $i} ?? null);

            if (! $name) {
                continue;
            }

            $children[] = array_filter([
                'slot' => $i,
                'name' => $name,
                'dob' => $this->formatChildDate($member->{'DTChild' . $i} ?? null),
                'sex' => $this->normalizeChildSex($member->{'child' . $i . 'Sex'} ?? null),
                'blood' => $this->normalizeChildBlood($member->{'child' . $i . 'Blood'} ?? null),
                'mobile' => $this->normalizeZeroValue($member->{'Child' . $i . 'Mobile'} ?? null),
                'email' => $this->normalizeEmail($member->{'Child' . $i . 'Email'} ?? null),
            ], fn($value) => $value !== null && $value !== '');
        }

        return $children;
    }

    private function formatChildDate(mixed $value): ?string
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

    private function normalizeChildSex(?string $value): ?string
    {
        return match (strtoupper((string) $this->normalizeZeroValue($value))) {
            'M' => 'Male',
            'F' => 'Female',
            default => null,
        };
    }

    private function normalizeChildBlood(?string $value): ?string
    {
        $value = $this->normalizeZeroValue($value);

        if (! $value) {
            return null;
        }

        return in_array(strtoupper($value), ['NO', 'N', 'NA', 'N/A'], true) ? null : $value;
    }

    private function normalizeEmail(?string $value): ?string
    {
        $value = $this->normalizeZeroValue($value);

        return $value && filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
    }

    private function normalizeZeroValue(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '0') {
            return null;
        }

        return $value;
    }

    private function toBool(mixed $value): bool
    {
        return in_array((string) $value, ['1', 'true', 'True'], true) || $value === true || $value === 1;
    }

    // ── Render ────────────────────────────────────────────
    public function render()
    {
        return view('livewire.member-profile');
    }
}
