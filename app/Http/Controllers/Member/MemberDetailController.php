<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Support\MemberProfileViewData;

class MemberDetailController extends Controller
{
    public function show(string $id)
    {
        return view('pages.member-detail', MemberProfileViewData::forMemberId($id));
    }
}
