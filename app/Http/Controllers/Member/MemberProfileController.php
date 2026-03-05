<?php

namespace App\Http\Controllers\Member;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class MemberProfileController extends Controller
{
    public function index()
    {
        $memberId = session('member.id');

        $member = DB::table('CustomerMst as c')
            ->leftJoin('List_MemExpType as mt', 'c.MemExpTypeID', '=', 'mt.MemExpTypeID')
            ->leftJoin('CusCardCatagory as cc', 'c.Cardid', '=', 'cc.Cardid')
            ->where('c.PrvCusID', $memberId)
            ->select([
                'c.PrvCusID',
                'c.Title',
                'c.CusName',
                'c.FName',
                'c.MName',
                'c.LName',
                'c.BloodGroup',
                'c.Phone',
                'c.Mobile',
                'c.Email',
                'c.Address',
                'c.City',
                'c.Profession',
                'c.Sex',
                'c.BirthDt',
                'c.DOE',          // join date
                'c.ExpDt',        // expiry date
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
                'c.Remarks',
                'mt.MemExpTypeName',  // member status e.g. Active, Expired
                'cc.Remarks as MemberCategory', // e.g. Permanent, Corporate
            ])
            ->first();

        if (! $member) {
            abort(404, 'Member not found.');
        }

        return view('pages.member-profile', compact('member'));
    }
}
