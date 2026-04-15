<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\SuccessfulPaymentTransaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LedgerController extends Controller
{
    public function index()
    {
        return view('pages.ledger');
    }

    public function data(): JsonResponse
    {
        $memberId = data_get(session('member'), 'id');

        if (! $memberId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $customerInfo = DB::table('CustomerMst')
            ->where('PrvCusID', $memberId)
            ->select('CreditAmt')
            ->first();

        $ledgerDue = DB::table('Customer_ledger')
            ->where('PrvCusId', $memberId)
            ->where('InvMRN', '<>', '0')
            ->selectRaw('COALESCE(SUM(COALESCE(DrAmt, 0) - COALESCE(CrAmt, 0)), 0) as Due')
            ->first();

        $ledgerRows = DB::table('Customer_Ledger as cl')
            ->join('List_Department as ld', 'cl.DepartmentID', '=', 'ld.Departmentid')
            ->where('cl.PrvCusID', $memberId)
            ->where('cl.InvMRN', '<>', '0')
            ->select([
                'cl.InvMRN',
                'cl.DrAmt',
                'cl.CrAmt',
                'cl.EDate',
                'cl.Remarks',
                'ld.Departmentname as DeptName',
            ])
            ->get();

        $monthlyHistory = $ledgerRows
            ->groupBy(fn ($row) => Carbon::parse($row->EDate)->format('Y-m'))
            ->map(function ($rows, $monthKey) {
                $dt = Carbon::createFromFormat('Y-m', $monthKey);
                $totalDebit = (float) $rows->sum(fn ($row) => (float) ($row->DrAmt ?? 0));
                $totalCredit = (float) $rows->sum(fn ($row) => (float) ($row->CrAmt ?? 0));

                return [
                    'month_key' => $monthKey,
                    'month_label' => $dt->format('F Y'),
                    'month_short' => $dt->format('M'),
                    'month_year' => $dt->format('Y'),
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                    'net' => $totalDebit - $totalCredit,
                    'row_count' => $rows->count(),
                ];
            })
            ->sortByDesc('month_key')
            ->values();

        $now = Carbon::now();
        $currentMonth = $monthlyHistory->firstWhere('month_key', $now->format('Y-m'));
        $deptBreakdown = DB::table('Customer_Ledger as a')
            ->join('List_Department as b', 'a.DepartmentID', '=', 'b.Departmentid')
            ->where('a.PrvCusID', $memberId)
            ->where('a.InvMRN', '<>', '0')
            ->whereRaw("CONVERT(char(7), a.EDate, 120) = ?", [$now->format('Y-m')])
            ->selectRaw('b.Departmentname as dept')
            ->selectRaw('SUM(COALESCE(a.DrAmt, 0)) as amount')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('b.Departmentname')
            ->orderBy('b.Departmentname')
            ->get()
            ->map(fn ($row) => [
                'dept' => $row->dept,
                'amount' => (float) ($row->amount ?? 0),
                'count' => (int) ($row->count ?? 0),
            ])
            ->values();

        $thisMonthDebit = (float) $deptBreakdown->sum('amount');
        $thisMonthCredit = (float) DB::table('Customer_Ledger as a')
            ->join('List_Department as b', 'a.DepartmentID', '=', 'b.Departmentid')
            ->where('a.PrvCusID', $memberId)
            ->where('a.InvMRN', '<>', '0')
            ->whereRaw("CONVERT(char(7), a.EDate, 120) = ?", [$now->format('Y-m')])
            ->sum('a.CrAmt');

        if ($thisMonthDebit <= 0 && $currentMonth) {
            $thisMonthDebit = (float) ($currentMonth['total_debit'] ?? 0);
        }

        $creditLimit = (float) ($customerInfo->CreditAmt ?? 0);
        $totalDue = max(0, (float) ($ledgerDue->Due ?? 0));
        $remaining = $creditLimit - $totalDue;
        $usagePercent = $creditLimit > 0
            ? min(100, (int) round(($totalDue / $creditLimit) * 100))
            : 0;

        $paymentTransactions = $this->paymentTransactions($memberId);
        $successfulTransactions = $this->successfulTransactions($memberId);

        return response()->json([
            'creditLimit' => $creditLimit,
            'totalDue' => $totalDue,
            'remaining' => $remaining,
            'thisMonthDebit' => $thisMonthDebit,
            'thisMonthCredit' => $thisMonthCredit,
            'usagePercent' => $usagePercent,
            'currentMonthLabel' => $now->format('F Y'),
            'deptBreakdown' => $deptBreakdown,
            'monthlyHistory' => $monthlyHistory,
            'paymentTransactions' => $paymentTransactions,
            'successfulTransactions' => $successfulTransactions,
        ]);
    }

    public function monthDetails(Request $request): JsonResponse
    {
        $memberId = data_get(session('member'), 'id');

        if (! $memberId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $monthKey = trim((string) $request->query('month'));

        if (! preg_match('/^\d{4}-\d{2}$/', $monthKey)) {
            return response()->json(['message' => 'Invalid month.'], 422);
        }

        $monthRows = DB::table('Customer_Ledger as cl')
            ->join('List_Department as ld', 'cl.DepartmentID', '=', 'ld.Departmentid')
            ->where('cl.PrvCusID', $memberId)
            ->where('cl.InvMRN', '<>', '0')
            ->whereRaw("CONVERT(char(7), cl.EDate, 120) = ?", [$monthKey])
            ->select([
                'cl.InvMRN',
                'cl.DrAmt',
                'cl.CrAmt',
                'cl.EDate',
                'cl.Remarks',
                'ld.Departmentname as DeptName',
            ])
            ->orderByDesc('cl.EDate')
            ->get();

        if ($monthRows->isEmpty()) {
            $monthData = DB::table('SMS_MonthlyBill')
            ->where('Prvcusid', $memberId)
            ->whereRaw("CONVERT(char(7), sMonth, 120) = ?", [$monthKey])
            ->select('MBill', 'Bal', 'sMonth')
            ->first();
            return response()->json([
                'month_key' => $monthKey,
                'month_label' => Carbon::createFromFormat('Y-m', $monthKey)->format('F Y'),
                'total_debit' => (float) ($monthData->MBill ?? 0),
                'total_credit' => 0.0,
                'depts' => collect($monthData ? [[
                    'dept' => 'Monthly Bill',
                    'total_debit' => (float) ($monthData->MBill ?? 0),
                    'total_credit' => 0.0,
                    'entries' => [[
                        'InvMRN' => null,
                        'DrAmt' => (float) ($monthData->MBill ?? 0),
                        'CrAmt' => 0.0,
                        'EDate' => Carbon::parse($monthData->sMonth)->format('M Y'),
                        'Remarks' => 'Monthly billed amount',
                    ], [
                        'InvMRN' => null,
                        'DrAmt' => 0.0,
                        'CrAmt' => 0.0,
                        'EDate' => Carbon::parse($monthData->sMonth)->format('M Y'),
                        'Remarks' => 'Closing balance: ' . number_format((float) ($monthData->Bal ?? 0), 2),
                    ]],
                ]] : [])->values(),
            ]);
        }

        $departmentRows = $monthRows
            ->groupBy(fn ($row) => $row->DeptName)
            ->map(fn ($rows, $dept) => [
                'dept' => $dept,
                'total_debit' => (float) $rows->sum(fn ($row) => (float) ($row->DrAmt ?? 0)),
                'total_credit' => (float) $rows->sum(fn ($row) => (float) ($row->CrAmt ?? 0)),
                'entries' => $rows->map(fn ($row) => [
                    'InvMRN' => $row->InvMRN,
                    'DrAmt' => (float) ($row->DrAmt ?? 0),
                    'CrAmt' => (float) ($row->CrAmt ?? 0),
                    'EDate' => Carbon::parse($row->EDate)->format('d M Y'),
                    'Remarks' => $row->Remarks ?: 'Ledger transaction',
                ])->values(),
            ])
            ->sortByDesc('total_debit')
            ->values();

        return response()->json([
            'month_key' => $monthKey,
            'month_label' => Carbon::createFromFormat('Y-m', $monthKey)->format('F Y'),
            'total_debit' => (float) $monthRows->sum(fn ($row) => (float) ($row->DrAmt ?? 0)),
            'total_credit' => (float) $monthRows->sum(fn ($row) => (float) ($row->CrAmt ?? 0)),
            'depts' => $departmentRows,
        ]);
    }

    private function paymentTransactions(string $memberId)
    {
        if (! Schema::hasTable('payment_transactions')) {
            return collect();
        }

        try {
            return PaymentTransaction::query()
                ->where('member_id', $memberId)
                ->latest('id')
                ->get()
                ->map(fn (PaymentTransaction $transaction) => [
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => (float) $transaction->amount,
                    'currency' => $transaction->currency,
                    'note' => $transaction->note,
                    'status' => $transaction->status,
                    'ssl_status' => $transaction->ssl_status,
                    'card_type' => $transaction->card_type,
                    'paid_at' => optional($transaction->paid_at)->format('d M Y h:i A'),
                    'updated_at' => optional($transaction->updated_at)->format('d M Y h:i A'),
                ])
                ->values();
        } catch (\Throwable $e) {
            Log::warning('Unable to load payment transactions for ledger.', [
                'member_id' => $memberId,
                'error' => $e->getMessage(),
            ]);

            return collect();
        }
    }

    private function successfulTransactions(string $memberId)
    {
        if (! Schema::hasTable('successful_payment_transactions')) {
            return collect();
        }

        try {
            return SuccessfulPaymentTransaction::query()
                ->where('member_id', $memberId)
                ->latest('id')
                ->get()
                ->map(fn (SuccessfulPaymentTransaction $transaction) => [
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => (float) $transaction->amount,
                    'currency' => $transaction->currency,
                    'note' => $transaction->note,
                    'card_type' => $transaction->card_type,
                    'bank_transaction_id' => $transaction->bank_transaction_id,
                    'paid_at' => optional($transaction->paid_at)->format('d M Y h:i A'),
                ])
                ->values();
        } catch (\Throwable $e) {
            Log::warning('Unable to load successful payment transactions for ledger.', [
                'member_id' => $memberId,
                'error' => $e->getMessage(),
            ]);

            return collect();
        }
    }
}
