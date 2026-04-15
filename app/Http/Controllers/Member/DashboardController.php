<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Support\PortalCache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    public function index()
    {
        $memberId = data_get(session('member'), 'id');

        if (! $memberId) {
            return redirect()->route('login');
        }

        $member = PortalCache::rememberResilient(
            "dashboard_member_{$memberId}_v1",
            "dashboard_member_{$memberId}_stale_v1",
            now()->addMinutes(10),
            now()->addDay(),
            function () use ($memberId) {
            return DB::table('CustomerMst as c')
                ->leftJoin('List_MemExpType as mt', 'c.MemExpTypeID', '=', 'mt.MemExpTypeID')
                ->leftJoin('CusCardCatagory as cc', 'c.Cardid', '=', 'cc.Cardid')
                ->where('c.PrvCusID', $memberId)
                ->select([
                    'c.PrvCusID',
                    'c.Title',
                    'c.CusName',
                    'c.CreditBal',
                    'c.CreditAmt',
                    'mt.MemExpTypeName',
                    'cc.Remarks as MemberCategory',
                ])
                ->first();
            },
            null
        );

        if (! $member) {
            Session::forget('member');

            return redirect()
                ->route('login')
                ->with('session_expired', 'Unable to load your member data. Please sign in again.');
        }

        $member->hasProfilePhoto = PortalCache::hasMemberPhoto($member->PrvCusID);
        $member->profilePhotoUrl = PortalCache::memberPhotoUrl($member->PrvCusID);
        $creditBal = null;
        $totalDue = null;

        return view('pages.dashboard', compact('member', 'totalDue', 'creditBal'));
    }

    public function summary()
    {
        $memberId = data_get(session('member'), 'id');

        if (! $memberId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        try {
            $ledger = PortalCache::rememberResilient(
                "dashboard_ledger_totals_{$memberId}_v2",
                "dashboard_ledger_totals_{$memberId}_stale_v2",
                now()->addMinutes(5),
                now()->addDay(),
                function () use ($memberId) {
                    return DB::table('Customer_ledger')
                        ->where('PrvCusId', $memberId)
                        ->where('InvMRN', '<>', '0')
                        ->selectRaw('COALESCE(SUM(COALESCE(DrAmt, 0) - COALESCE(CrAmt, 0)), 0) as Due')
                        ->first();
                },
                (object) ['Due' => 0]
            );
        } catch (\Throwable $e) {
            Log::warning('Unable to load dashboard ledger totals.', [
                'member_id' => $memberId,
                'error' => $e->getMessage(),
            ]);

            $ledger = (object) ['Due' => 0];
        }

        $totalDue = max(0, (float) ($ledger->Due ?? 0));

        try {
            $member = PortalCache::rememberResilient(
                "dashboard_member_{$memberId}_v1",
                "dashboard_member_{$memberId}_stale_v1",
                now()->addMinutes(10),
                now()->addDay(),
                fn () => DB::table('CustomerMst')
                    ->where('PrvCusID', $memberId)
                    ->select('CreditAmt', 'CreditBal')->first(),
                (object) ['CreditAmt' => 0, 'CreditBal' => 0]
            );
        } catch (\Throwable $e) {
            Log::warning('Unable to load dashboard member credit data.', [
                'member_id' => $memberId,
                'error' => $e->getMessage(),
            ]);

            $member = (object) ['CreditAmt' => 0, 'CreditBal' => 0];
        }

        $creditLimit = (float) ($member->CreditAmt ?? 0);
        $creditBal = $creditLimit - $totalDue;

        return response()->json([
            'creditBal' => $creditBal,
            'totalDue' => $totalDue,
            'creditLimit' => $creditLimit,
        ]);
    }
}
