<?php

namespace App\Http\Controllers\Member;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $memberId = data_get(session('member'), 'id');

        if (! $memberId) {
            return redirect()->route('login');
        }
 
        $member = DB::table('CustomerMst as c')
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
 
        // Real remaining = CreditAmt - (total debits - total credits) from ledger
        $ledger = DB::table('Customer_Ledger')
            ->where('PrvCusID', $memberId)
            ->whereNot('ACode','Opening')
            ->selectRaw('SUM(DrAmt) as total_debit, SUM(CrAmt) as total_credit')
            ->first();
 
        $totalDue      = max(0, (float)($ledger->total_debit ?? 0) - (float)($ledger->total_credit ?? 0));
        $creditBal     = (float)($member->CreditAmt ?? 0) - $totalDue;
 
        return view('pages.dashboard', compact('member', 'totalDue', 'creditBal'));
    }
}
