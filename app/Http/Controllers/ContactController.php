<?php

namespace App\Http\Controllers;

use App\Support\ContactDirectory;

class ContactController extends Controller
{
    public function index()
    {
        $groups = ContactDirectory::publicDirectory();

        $stats = [
            'departments' => count($groups),
            'lines' => collect($groups)->sum('total_entries'),
            'emails' => collect($groups)->sum('email_count'),
        ];

        return view('pages.contact', compact('groups', 'stats'));
    }
}
