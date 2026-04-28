<?php

use App\Support\MemberSession;

test('login page shows public legal page links', function () {
    $response = $this->get(route('login'));

    $response
        ->assertOk()
        ->assertSee('Club Policies')
        ->assertSee('Terms & Conditions')
        ->assertSee('Privacy Policy')
        ->assertSee('Return and Refund Policy')
        ->assertSee('Data Policy')
        ->assertSee('Contact Us');
});

test('public legal pages are accessible without authentication', function (string $routeName, string $title) {
    $response = $this->get(route($routeName));

    $response
        ->assertOk()
        ->assertSee($title);
})->with([
    ['legal.terms', 'Terms & Conditions'],
    ['legal.privacy', 'Privacy Policy'],
    ['legal.refund', 'Return and Refund Policy'],
    ['legal.data', 'Data Policy'],
    ['legal.contact', 'Contact Us'],
]);

test('public legal pages remain accessible with an active member session', function (string $routeName, string $title) {
    $response = $this
        ->withSession([
            MemberSession::KEY => MemberSession::build('CCL-1001', 'Test Member'),
        ])
        ->get(route($routeName));

    $response
        ->assertOk()
        ->assertSee($title)
        ->assertSee('Back to Dashboard');
})->with([
    ['legal.terms', 'Terms & Conditions'],
    ['legal.privacy', 'Privacy Policy'],
    ['legal.refund', 'Return and Refund Policy'],
    ['legal.data', 'Data Policy'],
    ['legal.contact', 'Contact Us'],
]);
