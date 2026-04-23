<?php

use App\Models\ClubContact;
use App\Support\ContactDirectory;

test('contact directory groups rows by department and sub department', function () {
    $contacts = [
        new ClubContact([
            'Sl' => 101,
            'Contact_ID' => '1',
            'Contact_Dept' => 'Main Club',
            'Contact_Sub_Dept' => null,
            'Phone' => '+88 02333388078',
            'Email' => null,
        ]),
        new ClubContact([
            'Sl' => 102,
            'Contact_ID' => '1',
            'Contact_Dept' => 'Main Club',
            'Contact_Sub_Dept' => null,
            'Phone' => '+88 02333388079',
            'Email' => null,
        ]),
        new ClubContact([
            'Sl' => 110,
            'Contact_ID' => '5',
            'Contact_Dept' => 'Guest House Complex',
            'Contact_Sub_Dept' => 'Guest House Direct',
            'Phone' => '+8801714080714',
            'Email' => 'ghcchittagongclub@gmail.com',
        ]),
    ];

    $groups = ContactDirectory::buildGroups($contacts);

    expect($groups)->toHaveCount(2)
        ->and($groups[0]['department'])->toBe('Main Club')
        ->and($groups[0]['total_entries'])->toBe(2)
        ->and($groups[0]['phone_count'])->toBe(2)
        ->and($groups[0]['email_count'])->toBe(0)
        ->and($groups[0]['subgroups'][0]['name'])->toBeNull()
        ->and($groups[0]['subgroups'][0]['entries'])->toHaveCount(2)
        ->and($groups[1]['department'])->toBe('Guest House Complex')
        ->and($groups[1]['subgroups'][0]['name'])->toBe('Guest House Direct')
        ->and($groups[1]['subgroups'][0]['entries'][0]['phone_href'])->toBe('tel:+8801714080714')
        ->and($groups[1]['subgroups'][0]['entries'][0]['email_href'])->toBe('mailto:ghcchittagongclub@gmail.com');
});
