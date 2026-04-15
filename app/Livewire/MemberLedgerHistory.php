<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Layout('layouts.app')]
#[Title('My Ledger — Chittagong Club Ltd.')]
class MemberLedgerHistory extends Component
{
    // ── UI state (changes on interaction) ─────────────────
    public string  $activeTab   = 'overview';
    public ?string $modalMonth  = null;
    public bool    $showModal   = false;

    // ── Data (loaded once in mount, never re-queried) ──────
    public float  $creditLimit       = 0;
    public float  $totalDue          = 0;
    public float  $remaining         = 0;
    public float  $thisMonthDebit    = 0;
    public float  $thisMonthCredit   = 0;
    public int    $usagePercent      = 0;
    public string $currentMonthLabel = '';

    public array $deptBreakdown  = [];
    public array $monthlyHistory = [];
    public array $rowsByMonth    = [];

    // ── Mount: runs ONCE on page load ─────────────────────
    public function mount(): void
    {
        $memberId = session('member')['id'];

        // 1. Credit limit
        $customerInfo      = DB::table('CustomerMst')
            ->where('PrvCusID', $memberId)
            ->select('CreditAmt', 'CreditBal')
            ->first();
        $this->creditLimit = (float) ($customerInfo->CreditAmt ?? 0);

        // 2. All ledger rows — single DB hit
        $allRows = DB::table('Customer_Ledger as cl')
            ->leftJoin('List_Department as ld', 'cl.DepartmentID', '=', 'ld.Departmentid')
            ->where('cl.PrvCusID', $memberId)
            ->select([
                'cl.id_customer_ledger_key',
                'cl.InvMRN',
                'cl.DrAmt',
                'cl.CrAmt',
                'cl.EDate',
                'cl.Remarks',
                'cl.Note',
                'cl.DepartmentID',
                'ld.DepartmentnameMaster as DeptName',
            ])
            ->orderBy('cl.EDate', 'desc')
            ->get();

        // 3. Current month
        $now        = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $now->copy()->endOfMonth();

        $this->currentMonthLabel = $now->format('F Y');

        $currentMonthRows = $allRows->filter(
            fn($row) => Carbon::parse($row->EDate)->between($monthStart, $monthEnd)
        );

        $this->thisMonthDebit  = (float) $currentMonthRows->sum('DrAmt');
        $this->thisMonthCredit = (float) $currentMonthRows->sum('CrAmt');

        // 4. All-time totals
        $ledgerDue = DB::table('Customer_Ledger')
            ->where('PrvCusId', $memberId)
            ->where('InvMRN', '<>', '0')
            ->selectRaw('COALESCE(SUM(COALESCE(DrAmt, 0) - COALESCE(CrAmt, 0)), 0) as Due')
            ->first();

        $this->totalDue     = max(0, (float) ($ledgerDue->Due ?? 0));
        $this->remaining    = $this->creditLimit - $this->totalDue;
        $this->usagePercent = $this->creditLimit > 0
            ? min(100, (int) round(($this->totalDue / $this->creditLimit) * 100))
            : 0;

        // 5. Dept breakdown for current month
        $this->deptBreakdown = $currentMonthRows
            ->where('DrAmt', '>', 0)
            ->groupBy('DeptName')
            ->map(fn($rows, $dept) => [
                'dept'   => $dept ?: 'General',
                'amount' => (float) $rows->sum('DrAmt'),
                'count'  => $rows->count(),
            ])
            ->sortByDesc('amount')
            ->values()
            ->toArray();

        // 6. Monthly history summary
        $this->monthlyHistory = $allRows
            ->groupBy(fn($row) => Carbon::parse($row->EDate)->format('Y-m'))
            ->map(function ($rows, $monthKey) {
                $dt = Carbon::createFromFormat('Y-m', $monthKey);
                return [
                    'month_key'    => $monthKey,
                    'month_label'  => $dt->format('F Y'),
                    'month_short'  => $dt->format('M'),
                    'month_year'   => $dt->format('Y'),
                    'total_debit'  => (float) $rows->sum('DrAmt'),
                    'total_credit' => (float) $rows->sum('CrAmt'),
                    'net'          => (float) ($rows->sum('DrAmt') - $rows->sum('CrAmt')),
                    'row_count'    => $rows->count(),
                ];
            })
            ->sortByDesc('month_key')
            ->values()
            ->toArray();

        // 7. Pre-build rowsByMonth for instant modal (zero DB cost on open)
        foreach ($allRows->groupBy(fn($row) => Carbon::parse($row->EDate)->format('Y-m')) as $monthKey => $rows) {
            $this->rowsByMonth[$monthKey] = $rows
                ->groupBy('DeptName')
                ->map(fn($dRows, $dept) => [
                    'dept'         => $dept ?: 'General',
                    'total_debit'  => (float) $dRows->sum('DrAmt'),
                    'total_credit' => (float) $dRows->sum('CrAmt'),
                    'entries'      => $dRows->map(fn($r) => [
                        'InvMRN'  => $r->InvMRN,
                        'DrAmt'   => (float) $r->DrAmt,
                        'CrAmt'   => (float) $r->CrAmt,
                        'EDate'   => $r->EDate,
                        'Remarks' => $r->Remarks,
                        'Note'    => $r->Note,
                    ])->values()->toArray(),
                ])
                ->sortByDesc('total_debit')
                ->values()
                ->toArray();
        }
    }

    // ── Actions — no DB, instant ──────────────────────────
    public function openModal(string $monthKey): void
    {
        $this->modalMonth = $monthKey;
        $this->showModal  = true;
    }

    public function closeModal(): void
    {
        $this->showModal  = false;
        $this->modalMonth = null;
    }

    // ── Render — no DB, just passes view ─────────────────
    public function render()
    {
        return view('livewire.member-ledger', [
            'modalData' => $this->showModal && $this->modalMonth
                ? ($this->rowsByMonth[$this->modalMonth] ?? [])
                : [],
        ]);
    }
}
