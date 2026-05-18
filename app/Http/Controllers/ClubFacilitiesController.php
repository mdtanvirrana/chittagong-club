<?php

namespace App\Http\Controllers;

use App\Support\ClubFacilities;

class ClubFacilitiesController extends Controller
{
    public function index()
    {
        $facilities = collect(ClubFacilities::all());

        return view('pages.club-facilities', compact('facilities'));
    }
}
