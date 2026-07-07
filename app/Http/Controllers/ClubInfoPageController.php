<?php

namespace App\Http\Controllers;

use App\Support\PortalImageDirectory;
use App\Support\PortalPageImages;
use Illuminate\View\View;

class ClubInfoPageController extends Controller
{
    public function dressCode(): View
    {
        return view('pages.dress-code', [
            'images' => PortalPageImages::urls(PortalImageDirectory::DRESS_CODE_DIRECTORY),
        ]);
    }

    public function generalRules(): View
    {
        return view('pages.general-rules', [
            'images' => PortalPageImages::urls(PortalImageDirectory::GENERAL_RULES_DIRECTORY),
        ]);
    }
}
