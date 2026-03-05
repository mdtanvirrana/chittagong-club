<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Layout('layouts.app')]
#[Title('My Profile — Chittagong Club Ltd.')]
class MemberProfile extends Component
{
    // ── Public properties ─────────────────────────────────
    public object $member;

    public string $fullName    = '';
    public string $initials    = '';
    public string $joinDate    = '—';
    public string $birthDate   = '—';
    public string $age         = '—';
    public string $weddingDt   = '—';
    public bool   $isMarried   = false;
    public string $statusColor = '';

    // ── Lifecycle ─────────────────────────────────────────
    public function mount(): void
    {
        $memberId = session('member')['id'];

        $member = DB::table('CustomerMst as c')
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
            'active'  => 'bg-green-500/20 text-green-400',
            'expired' => 'bg-red-500/20 text-red-400',
            default   => 'bg-amber-500/20 text-amber-400',
        };
    }

    // ── Render ────────────────────────────────────────────
    public function render()
    {
        return view('livewire.member-profile');
    }
}
