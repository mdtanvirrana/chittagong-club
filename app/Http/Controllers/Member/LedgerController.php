<?php

namespace App\Http\Controllers\Member;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class LedgerController extends Controller
{
    public function index()
    {
        $memberId = session('member')['id'];

        // 1. Credit limit
        $customerInfo = DB::table('CustomerMst')
            ->where('PrvCusID', $memberId)
            ->select('CreditAmt', 'CreditBal')
            ->first();

        $creditLimit = (float) ($customerInfo->CreditAmt ?? 0);

        // 2. All ledger rows — single DB hit
        $allRows = DB::table('Customer_Ledger as cl')
        ->whereNot('cl.ACode','Opening')
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

        // 3. Current month rows
        $now        = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $now->copy()->endOfMonth();

        $currentMonthRows = $allRows->filter(
            fn($r) => Carbon::parse($r->EDate)->between($monthStart, $monthEnd)
        );

        $thisMonthDebit  = (float) $currentMonthRows->sum('DrAmt');
        $thisMonthCredit = (float) $currentMonthRows->sum('CrAmt');

        // 4. All-time totals
        $totalDebit  = (float) $allRows->sum('DrAmt');
        $totalCredit = (float) $allRows->sum('CrAmt');
        $totalDue    = max(0, $totalDebit - $totalCredit);
        $remaining   = $creditLimit - $totalDue;
        $usagePercent = $creditLimit > 0
            ? min(100, (int) round(($totalDue / $creditLimit) * 100))
            : 0;

        // 5. Dept breakdown for current month
        $deptBreakdown = $currentMonthRows
            ->where('DrAmt', '>', 0)
            ->groupBy('DeptName')
            ->map(fn($rows, $dept) => [
                'dept'   => $dept ?: 'General',
                'amount' => (float) $rows->sum('DrAmt'),
                'count'  => $rows->count(),
            ])
            ->sortByDesc('amount')
            ->values();

        // 6. Monthly history — grouped, with dept detail inside
        $monthlyHistory = $allRows
            ->groupBy(fn($r) => Carbon::parse($r->EDate)->format('Y-m'))
            ->map(function ($rows, $monthKey) {
                $dt = Carbon::createFromFormat('Y-m', $monthKey);

                // dept breakdown for this month (for modal)
                $depts = $rows
                    ->groupBy('DeptName')
                    ->map(fn($dRows, $dept) => [
                        'dept'         => $dept ?: 'General',
                        'total_debit'  => (float) $dRows->sum('DrAmt'),
                        'total_credit' => (float) $dRows->sum('CrAmt'),
                        'entries'      => $dRows->map(fn($r) => [
                            'InvMRN'  => $r->InvMRN,
                            'DrAmt'   => (float) $r->DrAmt,
                            'CrAmt'   => (float) $r->CrAmt,
                            'EDate'   => Carbon::parse($r->EDate)->format('d M'),
                            'Remarks' => $r->Remarks ?: ($r->Note ?: $r->InvMRN ?: '—'),
                        ])->values()->all(),
                    ])
                    ->sortByDesc('total_debit')
                    ->values()
                    ->all();

                return [
                    'month_key'    => $monthKey,
                    'month_label'  => $dt->format('F Y'),
                    'month_short'  => $dt->format('M'),
                    'month_year'   => $dt->format('Y'),
                    'total_debit'  => (float) $rows->sum('DrAmt'),
                    'total_credit' => (float) $rows->sum('CrAmt'),
                    'net'          => (float) ($rows->sum('DrAmt') - $rows->sum('CrAmt')),
                    'row_count'    => $rows->count(),
                    'depts'        => $depts,
                ];
            })
            ->sortByDesc('month_key')
            ->values();
$currentMonthLabel=null;
        return view('pages.ledger', compact(
            'creditLimit', 'totalDue', 'remaining',
            'thisMonthDebit', 'thisMonthCredit',
            'usagePercent', 'deptBreakdown',
            'monthlyHistory',
            'currentMonthLabel'
        ) + ['currentMonthLabel' => $now->format('F Y')]);
    }
}