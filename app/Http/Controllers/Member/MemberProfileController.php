<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Support\MemberProfileViewData;

class MemberProfileController extends Controller
{
    public function index()
    {
        return view('pages.member-profile', MemberProfileViewData::forCurrentMember());
    }
}
