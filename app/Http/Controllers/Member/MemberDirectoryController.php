<?php

namespace App\Http\Controllers\Member;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
class MemberDirectoryController extends Controller
{
     public function index()
    {
        $rows = DB::table('CustomerMst as c')
            ->leftJoin('CusCardCatagory as cc', 'c.Cardid', '=', 'cc.Cardid')
            ->where('c.MemExpTypeID', 100)
            ->whereIn('c.Cardid', [101])
            ->orderBy('c.CusName')
            ->select('c.PrvCusID', 'c.CusName', 'cc.Remarks as MemberCategory')
            ->get();
 
        $members = $rows->map(function ($m) {
            $words    = array_values(array_filter(explode(' ', trim($m->CusName))));
            $initials = implode('', array_map(
                fn($w) => strtoupper(mb_substr($w, 0, 1)),
                array_slice($words, 0, 2)
            ));
            return [
                'id'       => (string) $m->PrvCusID,
                'name'     => (string) $m->CusName,
                'category' => (string) ($m->MemberCategory ?? ''),
                'initials' => $initials,
            ];
        })->values()->all();
 
        // Safely encode — HEX flags prevent ANY special char from breaking Blade/JS
        $membersJson = json_encode(
            $members,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE
        );
 
        return view('pages.member-directory', [
            'membersJson' => $membersJson,
            'total'       => count($members),
        ]);
    }
}