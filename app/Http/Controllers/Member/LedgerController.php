<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Support\PortalCache;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LedgerController extends Controller
{
    public function index()
    {
        $memberId = session('member')['id'];

        $data = PortalCache::rememberResilient(
            "ledger_page_{$memberId}_v2",
            "ledger_page_{$memberId}_stale_v1",
            now()->addMinutes(5),
            now()->addDay(),
            function () use ($memberId): array {
                $customerInfo = DB::table('CustomerMst')
                    ->where('PrvCusID', $memberId)
                    ->select('CreditAmt', 'CreditBal')
                    ->first();

                $allRows = DB::table('Customer_Ledger as cl')
                    ->whereNot('cl.ACode', 'Opening')
                    ->leftJoin('List_Department as ld', 'cl.DepartmentID', '=', 'ld.Departmentid')
                    ->where('cl.PrvCusID', $memberId)
                    ->select([
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

                return $this->buildLedgerPayload($customerInfo, $allRows);
            },
            $this->emptyLedgerPayload()
        );

        return view('pages.ledger', $data);
    }

    private function buildLedgerPayload(object|null $customerInfo, Collection $allRows): array
    {
        $creditLimit = (float) ($customerInfo->CreditAmt ?? 0);
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $currentMonthRows = $allRows->filter(
            fn ($row) => Carbon::parse($row->EDate)->between($monthStart, $monthEnd)
        );

        $thisMonthDebit = (float) $currentMonthRows->sum('DrAmt');
        $thisMonthCredit = (float) $currentMonthRows->sum('CrAmt');
        $totalDebit = (float) $allRows->sum('DrAmt');
        $totalCredit = (float) $allRows->sum('CrAmt');
        $totalDue = max(0, $totalDebit - $totalCredit);
        $remaining = $creditLimit - $totalDue;
        $usagePercent = $creditLimit > 0
            ? min(100, (int) round(($totalDue / $creditLimit) * 100))
            : 0;

        $deptBreakdown = $currentMonthRows
            ->where('DrAmt', '>', 0)
            ->groupBy('DeptName')
            ->map(fn ($rows, $dept) => [
                'dept' => $dept ?: 'General',
                'amount' => (float) $rows->sum('DrAmt'),
                'count' => $rows->count(),
            ])
            ->sortByDesc('amount')
            ->values();

        $monthlyHistory = $allRows
            ->groupBy(fn ($row) => Carbon::parse($row->EDate)->format('Y-m'))
            ->map(function ($rows, $monthKey) {
                $dt = Carbon::createFromFormat('Y-m', $monthKey);

                $depts = $rows
                    ->groupBy('DeptName')
                    ->map(fn ($departmentRows, $dept) => [
                        'dept' => $dept ?: 'General',
                        'total_debit' => (float) $departmentRows->sum('DrAmt'),
                        'total_credit' => (float) $departmentRows->sum('CrAmt'),
                        'entries' => $departmentRows->map(fn ($row) => [
                            'InvMRN' => $row->InvMRN,
                            'DrAmt' => (float) $row->DrAmt,
                            'CrAmt' => (float) $row->CrAmt,
                            'EDate' => Carbon::parse($row->EDate)->format('d M'),
                            'Remarks' => $row->Remarks ?: ($row->Note ?: $row->InvMRN ?: '—'),
                        ])->values()->all(),
                    ])
                    ->sortByDesc('total_debit')
                    ->values()
                    ->all();

                return [
                    'month_key' => $monthKey,
                    'month_label' => $dt->format('F Y'),
                    'month_short' => $dt->format('M'),
                    'month_year' => $dt->format('Y'),
                    'total_debit' => (float) $rows->sum('DrAmt'),
                    'total_credit' => (float) $rows->sum('CrAmt'),
                    'net' => (float) ($rows->sum('DrAmt') - $rows->sum('CrAmt')),
                    'row_count' => $rows->count(),
                    'depts' => $depts,
                ];
            })
            ->sortByDesc('month_key')
            ->values();

        return compact(
            'creditLimit',
            'totalDue',
            'remaining',
            'thisMonthDebit',
            'thisMonthCredit',
            'usagePercent',
            'deptBreakdown',
            'monthlyHistory'
        ) + ['currentMonthLabel' => $now->format('F Y')];
    }

    private function emptyLedgerPayload(): array
    {
        return [
            'creditLimit' => 0.0,
            'totalDue' => 0.0,
            'remaining' => 0.0,
            'thisMonthDebit' => 0.0,
            'thisMonthCredit' => 0.0,
            'usagePercent' => 0,
            'deptBreakdown' => collect(),
            'monthlyHistory' => collect(),
            'currentMonthLabel' => Carbon::now()->format('F Y'),
        ];
    }
}
