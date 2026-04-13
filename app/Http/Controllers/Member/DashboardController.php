<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Support\PortalCache;
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
        $member->profilePhotoUrl = $member->hasProfilePhoto
            ? asset('images/' . $member->PrvCusID . '.jpg')
            : null;
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

        $ledger = PortalCache::rememberResilient(
            "dashboard_ledger_totals_{$memberId}_v1",
            "dashboard_ledger_totals_{$memberId}_stale_v1",
            now()->addMinutes(5),
            now()->addDay(),
            function () use ($memberId) {
                return DB::table('Customer_Ledger')
                    ->where('PrvCusID', $memberId)
                    ->whereNot('ACode', 'Opening')
                    ->selectRaw('SUM(DrAmt) as total_debit, SUM(CrAmt) as total_credit')
                    ->first();
            },
            (object) ['total_debit' => 0, 'total_credit' => 0]
        );

        $totalDue = max(0, (float) ($ledger->total_debit ?? 0) - (float) ($ledger->total_credit ?? 0));

        $member = PortalCache::rememberResilient(
            "dashboard_member_{$memberId}_v1",
            "dashboard_member_{$memberId}_stale_v1",
            now()->addMinutes(10),
            now()->addDay(),
            fn () => DB::table('CustomerMst')->where('PrvCusID', $memberId)->select('CreditAmt')->first(),
            (object) ['CreditAmt' => 0]
        );

        $creditLimit = (float) ($member->CreditAmt ?? 0);
        $creditBal = $creditLimit - $totalDue;

        return response()->json([
            'creditBal' => $creditBal,
            'totalDue' => $totalDue,
            'creditLimit' => $creditLimit,
        ]);
    }
}
