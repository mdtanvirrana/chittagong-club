<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Support\PortalCache;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Member Detail — Chittagong Club Ltd.')]
class MemberDetail extends Component
{
    public object $member;

    public string $fullName    = '';
    public string $initials    = '';
    public string $joinDate    = '—';
    public string $birthDate   = '—';
    public string $age         = '—';
    public string $weddingDt   = '—';
    public bool   $isMarried   = false;
    public string $statusColor = '';
    public ?string $callHref = null;
    public ?string $smsHref = null;
    public ?string $emailHref = null;

    public function mount(string $id): void
    {
        $member = PortalCache::remember("member_detail_{$id}_v1", now()->addMinutes(10), function () use ($id) {
            return DB::table('CustomerMst as c')
                ->leftJoin('List_MemExpType as mt', 'c.MemExpTypeID', '=', 'mt.MemExpTypeID')
                ->leftJoin('CusCardCatagory as cc', 'c.Cardid', '=', 'cc.Cardid')
                ->where('c.PrvCusID', $id)
                ->select([
                    'c.PrvCusID', 'c.Title', 'c.CusName',
                    'c.BloodGroup', 'c.Phone', 'c.Mobile', 'c.Email', 'c.Address',
                    'c.Profession', 'c.Sex', 'c.BirthDt', 'c.DOE', 'c.ExpDt',
                    'c.MaritalStatus', 'c.MarriageDT',
                    'c.SpouseName', 'c.SpoBlood', 'c.SpoMobile',
                    'c.NoChild', 'c.Child1', 'c.Child2', 'c.Child3',
                    'c.FatherName', 'c.MotherName',
                    'c.Religion', 'c.Nationality', 'c.NID', 'c.PassportNo',
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
        $this->compute();
    }

    private function compute(): void
    {
        $m = $this->member;

        $this->fullName = trim(($m->Title ? $m->Title . ' ' : '') . $m->CusName);

        $this->initials = collect(explode(' ', $m->CusName))
            ->map(fn($w) => strtoupper($w[0] ?? ''))
            ->take(2)->join('');

        $this->joinDate  = $m->DOE        ? Carbon::parse($m->DOE)->format('M d, Y')        : '—';
        $this->birthDate = $m->BirthDt    ? Carbon::parse($m->BirthDt)->format('M d, Y')    : '—';
        $this->age       = $m->BirthDt    ? Carbon::parse($m->BirthDt)->age . ' yrs'        : '—';
        $this->weddingDt = $m->MarriageDT ? Carbon::parse($m->MarriageDT)->format('M d, Y') : '—';

        $this->isMarried = in_array(strtolower($m->MaritalStatus ?? ''), ['m', 'married']);

        $this->statusColor = match (strtolower($m->MemExpTypeName ?? '')) {
            'active'  => 'bg-green-500/20 text-green-400',
            'expired' => 'bg-red-500/20 text-red-400',
            default   => 'bg-amber-500/20 text-amber-400',
        };

        $callNumber = $m->Mobile ?: $m->Phone;

        $this->callHref = $this->buildPhoneHref($callNumber, 'tel');
        $this->smsHref = $this->buildPhoneHref($callNumber, 'sms');
        $this->emailHref = $this->buildEmailHref($m->Email ?? null);
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

    public function render()
    {
        return view('livewire.member-detail');
    }
}
