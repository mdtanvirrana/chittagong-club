<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\SuccessfulPaymentTransaction;
use App\Support\MemberAccess;
use App\Support\NotifyOutbox;
use App\Support\PortalCache;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LedgerController extends Controller
{
    public function index()
    {
        return view('pages.ledger');
    }

    public function data(Request $request): JsonResponse
    {
        $memberId = $this->memberId($request);

        if (! $memberId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $payload = [
            ...$this->ledgerSummaryPayload($memberId),
            ...$this->ledgerInsightsPayload($memberId),
            ...$this->ledgerHistoryPayload($memberId, $request),
            ...$this->ledgerPaymentsPayload($memberId, $request),
        ];

        NotifyOutbox::dueReminder(
            $memberId,
            (float) ($payload['totalDue'] ?? 0),
            (float) ($payload['creditLimit'] ?? 0)
        );

        return PortalCache::noStoreJson($payload);
    }

    public function summary(Request $request): JsonResponse
    {
        $memberId = $this->memberId($request);

        if (! $memberId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $payload = $this->ledgerSummaryPayload($memberId);

        NotifyOutbox::dueReminder(
            $memberId,
            (float) ($payload['totalDue'] ?? 0),
            (float) ($payload['creditLimit'] ?? 0)
        );

        return PortalCache::noStoreJson($payload);
    }

    public function insights(Request $request): JsonResponse
    {
        $memberId = $this->memberId($request);

        if (! $memberId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $payload = $this->ledgerInsightsPayload($memberId);

        return PortalCache::noStoreJson($payload);
    }

    public function history(Request $request): JsonResponse
    {
        $memberId = $this->memberId($request);

        if (! $memberId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $payload = $this->ledgerHistoryPayload($memberId, $request);

        return PortalCache::noStoreJson($payload);
    }

    public function payments(Request $request): JsonResponse
    {
        $memberId = $this->memberId($request);

        if (! $memberId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $payload = $this->ledgerPaymentsPayload($memberId, $request);

        return PortalCache::noStoreJson($payload);
    }

    public function monthDetails(Request $request): JsonResponse
    {
        $memberId = $this->memberId($request);

        if (! $memberId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $monthKey = trim((string) $request->query('month'));

        if (! preg_match('/^\d{4}-\d{2}$/', $monthKey)) {
            return response()->json(['message' => 'Invalid month.'], 422);
        }

        [$monthStart, $monthEnd] = $this->monthDateRange($monthKey);

        $monthRows = DB::table('Customer_Ledger as cl')
            ->join('List_Department as ld', 'cl.DepartmentID', '=', 'ld.Departmentid')
            ->where('cl.PrvCusID', $memberId)
            ->where('cl.InvMRN', '<>', '0')
            ->whereBetween('cl.EDate', [$monthStart, $monthEnd])
            ->select([
                'cl.InvMRN',
                'cl.DrAmt',
                'cl.CrAmt',
                'cl.EDate',
                'cl.Remarks',
                'cl.Note',
                'ld.Departmentname as DeptName',
            ])
            ->orderByDesc('cl.EDate')
            ->get();

        if ($monthRows->isEmpty()) {
            $monthData = DB::table('SMS_MonthlyBill')
            ->where('Prvcusid', $memberId)
            ->whereBetween('sMonth', [$monthStart, $monthEnd])
            ->select('MBill', 'Bal', 'sMonth')
            ->first();
            return PortalCache::noStoreJson([
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
                    'Note' => null,
                    ], [
                        'InvMRN' => null,
                        'DrAmt' => 0.0,
                        'CrAmt' => 0.0,
                        'EDate' => Carbon::parse($monthData->sMonth)->format('M Y'),
                        'Remarks' => 'Closing balance: ' . number_format((float) ($monthData->Bal ?? 0), 2),
                        'Note' => null,
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
                    'Note' => $row->Note,
                ])->values(),
            ])
            ->sortByDesc('total_debit')
            ->values();

        return PortalCache::noStoreJson([
            'month_key' => $monthKey,
            'month_label' => Carbon::createFromFormat('Y-m', $monthKey)->format('F Y'),
            'total_debit' => (float) $monthRows->sum(fn ($row) => (float) ($row->DrAmt ?? 0)),
            'total_credit' => (float) $monthRows->sum(fn ($row) => (float) ($row->CrAmt ?? 0)),
            'depts' => $departmentRows,
        ]);
    }

    private function ledgerPaymentsPayload(string $memberId, Request $request): array
    {
        [$page, $perPage] = $this->paginationParams($request);
        $paymentTransactions = $this->paymentTransactions($memberId, $page, $perPage);
        $successfulTransactions = $this->successfulTransactions($memberId, $page, $perPage);

        return [
            'paymentTransactions' => $paymentTransactions['items'],
            'successfulTransactions' => $successfulTransactions['items'],
            'payment_transactions_pagination' => $this->paginationPayload($page, $perPage, $paymentTransactions['total'], $paymentTransactions['items']->count()),
            'successful_transactions_pagination' => $this->paginationPayload($page, $perPage, $successfulTransactions['total'], $successfulTransactions['items']->count()),
        ];
    }

    private function paymentTransactions(string $memberId, int $page, int $perPage): array
    {
        try {
            $query = PaymentTransaction::query()->where('member_id', $memberId);
            $total = (clone $query)->count();
            $items = $query
                ->latest('id')
                ->forPage($page, $perPage)
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

            return ['items' => $items, 'total' => $total];
        } catch (\Throwable $e) {
            Log::warning('Unable to load payment transactions for ledger.', [
                'member_id' => $memberId,
                'error' => $e->getMessage(),
            ]);

            return ['items' => collect(), 'total' => 0];
        }
    }

    private function successfulTransactions(string $memberId, int $page, int $perPage): array
    {
        try {
            $query = SuccessfulPaymentTransaction::query()->where('member_id', $memberId);
            $total = (clone $query)->count();
            $items = $query
                ->latest('id')
                ->forPage($page, $perPage)
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

            return ['items' => $items, 'total' => $total];
        } catch (\Throwable $e) {
            Log::warning('Unable to load successful payment transactions for ledger.', [
                'member_id' => $memberId,
                'error' => $e->getMessage(),
            ]);

            return ['items' => collect(), 'total' => 0];
        }
    }

    private function ledgerSummaryPayload(string $memberId): array
    {
        $customerInfo = MemberAccess::activeMemberQuery('c', 'cc')
            ->where('c.PrvCusID', $memberId)
            ->select([
                'c.CreditAmt',
                DB::raw("(SELECT COALESCE(SUM(COALESCE(cl.DrAmt, 0) - COALESCE(cl.CrAmt, 0)), 0) FROM Customer_ledger cl WHERE cl.PrvCusId = c.PrvCusID AND cl.InvMRN <> '0') as LedgerDue"),
            ])
            ->first();

        $creditLimit = (float) ($customerInfo->CreditAmt ?? 0);
        $totalDue = max(0, (float) ($customerInfo->LedgerDue ?? 0));
        $remaining = $creditLimit - $totalDue;
        $usagePercent = $creditLimit > 0
            ? min(100, (int) round(($totalDue / $creditLimit) * 100))
            : 0;

        return [
            'creditLimit' => $creditLimit,
            'totalDue' => $totalDue,
            'remaining' => $remaining,
            'usagePercent' => $usagePercent,
        ];
    }

    private function ledgerInsightsPayload(string $memberId): array
    {
        $now = Carbon::now();
        [$currentMonthStart, $currentMonthEnd] = $this->monthDateRange($now->format('Y-m'));

        $deptBreakdown = DB::table('Customer_Ledger as a')
            ->leftJoin('List_Department as b', 'a.DepartmentID', '=', 'b.Departmentid')
            ->where('a.PrvCusID', $memberId)
            ->where('a.InvMRN', '<>', '0')
            ->whereBetween('a.EDate', [$currentMonthStart, $currentMonthEnd])
            ->selectRaw('b.Departmentname as dept')
            ->selectRaw('SUM(COALESCE(a.DrAmt, 0)) as debit_amount')
            ->selectRaw('SUM(COALESCE(a.CrAmt, 0)) as credit_amount')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('b.Departmentname')
            ->get()
            ->map(fn ($row) => [
                'dept' => $row->dept ?: 'General',
                'debit_amount' => (float) ($row->debit_amount ?? 0),
                'credit_amount' => (float) ($row->credit_amount ?? 0),
                'count' => (int) ($row->count ?? 0),
            ])
            ->filter(fn (array $row) => $row['debit_amount'] > 0 || $row['credit_amount'] > 0)
            ->sortByDesc(fn (array $row) => max($row['debit_amount'], $row['credit_amount']))
            ->values();

        $thisMonthDebit = (float) $deptBreakdown->sum('debit_amount');
        $thisMonthCredit = (float) $deptBreakdown->sum('credit_amount');

        if ($thisMonthDebit <= 0 && $deptBreakdown->isEmpty()) {
            $monthData = $this->monthlyBillForRange($memberId, $currentMonthStart, $currentMonthEnd);

            if ($monthData) {
                $thisMonthDebit = (float) ($monthData->MBill ?? 0);
                $deptBreakdown = collect([[
                    'dept' => 'Monthly Bill',
                    'debit_amount' => $thisMonthDebit,
                    'credit_amount' => 0.0,
                    'count' => 1,
                ]])->filter(fn (array $row) => $row['debit_amount'] > 0)->values();
            }
        }

        return [
            'thisMonthDebit' => $thisMonthDebit,
            'thisMonthCredit' => $thisMonthCredit,
            'currentMonthLabel' => $now->format('F Y'),
            'deptBreakdown' => $deptBreakdown,
        ];
    }

    private function ledgerHistoryPayload(string $memberId, Request $request): array
    {
        [$page, $perPage] = $this->paginationParams($request);
        $monthlyRows = DB::table('Customer_Ledger as cl')
            ->where('cl.PrvCusID', $memberId)
            ->where('cl.InvMRN', '<>', '0')
            ->where('cl.ACode', '<>', 'Opening')
            ->whereNotNull('cl.EDate')
            ->selectRaw('YEAR(cl.EDate) as ledger_year')
            ->selectRaw('MONTH(cl.EDate) as ledger_month')
            ->selectRaw('SUM(COALESCE(cl.DrAmt, 0)) as total_debit')
            ->selectRaw('SUM(COALESCE(cl.CrAmt, 0)) as total_credit')
            ->selectRaw('COUNT(*) as row_count')
            ->groupByRaw('YEAR(cl.EDate), MONTH(cl.EDate)')
            ->orderByRaw('YEAR(cl.EDate) DESC, MONTH(cl.EDate) DESC')
            ->get();

        if ($monthlyRows->isEmpty()) {
            $history = $this->monthlyBillHistory($memberId);
            $pageHistory = $history->forPage($page, $perPage)->values();

            return [
                'monthlyHistory' => $pageHistory,
                'pagination' => $this->paginationPayload($page, $perPage, $history->count(), $pageHistory->count()),
            ];
        }

        $history = $monthlyRows
            ->map(function (object $row): array {
                $dt = Carbon::create((int) $row->ledger_year, (int) $row->ledger_month, 1);
                $totalDebit = (float) ($row->total_debit ?? 0);
                $totalCredit = (float) ($row->total_credit ?? 0);

                return [
                    'month_key' => $dt->format('Y-m'),
                    'month_label' => $dt->format('F Y'),
                    'month_short' => $dt->format('M'),
                    'month_year' => $dt->format('Y'),
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                    'net' => $totalDebit - $totalCredit,
                    'row_count' => (int) ($row->row_count ?? 0),
                ];
            })
            ->values();

        $pageHistory = $history->forPage($page, $perPage)->values();

        return [
            'monthlyHistory' => $pageHistory,
            'pagination' => $this->paginationPayload($page, $perPage, $history->count(), $pageHistory->count()),
        ];
    }

    private function monthlyBillHistory(string $memberId)
    {
        try {
            return DB::table('SMS_MonthlyBill')
                ->where('Prvcusid', $memberId)
                ->whereNotNull('sMonth')
                ->select('MBill', 'Bal', 'sMonth')
                ->orderByDesc('sMonth')
                ->limit(24)
                ->get()
                ->map(function ($row) {
                    $dt = Carbon::parse($row->sMonth);
                    $totalDebit = (float) ($row->MBill ?? 0);

                    return [
                        'month_key' => $dt->format('Y-m'),
                        'month_label' => $dt->format('F Y'),
                        'month_short' => $dt->format('M'),
                        'month_year' => $dt->format('Y'),
                        'total_debit' => $totalDebit,
                        'total_credit' => 0.0,
                        'net' => $totalDebit,
                        'row_count' => 1,
                    ];
                })
                ->filter(fn (array $row) => $row['total_debit'] > 0)
                ->values();
        } catch (\Throwable $e) {
            Log::warning('Unable to load monthly bill fallback history for ledger.', [
                'member_id' => $memberId,
                'error' => $e->getMessage(),
            ]);

            return collect();
        }
    }

    private function monthlyBillForRange(string $memberId, Carbon $monthStart, Carbon $monthEnd): ?object
    {
        try {
            return DB::table('SMS_MonthlyBill')
                ->where('Prvcusid', $memberId)
                ->whereBetween('sMonth', [$monthStart, $monthEnd])
                ->select('MBill', 'Bal', 'sMonth')
                ->first();
        } catch (\Throwable $e) {
            Log::warning('Unable to load monthly bill fallback insight for ledger.', [
                'member_id' => $memberId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function monthDateRange(string $monthKey): array
    {
        $monthStart = Carbon::createFromFormat('Y-m', $monthKey)->startOfMonth()->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth()->endOfDay();

        return [$monthStart, $monthEnd];
    }

    private function paginationParams(Request $request): array
    {
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = min(max((int) $request->integer('per_page', $request->integer('limit', 20)), 1), 20);

        return [$page, $perPage];
    }

    private function paginationPayload(int $page, int $perPage, int $total, int $pageCount): array
    {
        $lastPage = max(1, (int) ceil($total / max(1, $perPage)));
        $from = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;

        return [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
            'has_more' => $page < $lastPage,
            'from' => $from,
            'to' => $from === 0 ? 0 : min($from + $pageCount - 1, $total),
        ];
    }

    private function memberId(Request $request): ?string
    {
        $apiMemberId = trim((string) data_get($request->user(), 'member_id'));

        if ($apiMemberId !== '') {
            return $apiMemberId;
        }

        $sessionMemberId = trim((string) data_get(session('member'), 'id'));

        return $sessionMemberId !== '' ? $sessionMemberId : null;
    }
}
