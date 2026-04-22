<?php

use App\Support\MemberSession;

test('members can access protected routes before the one week expiry', function () {
    $response = $this->withSession([
        MemberSession::KEY => MemberSession::build('CCL-1001', 'Test Member'),
    ])->get('/about');

    $response->assertOk();
});

test('expired member sessions are logged out from protected routes', function () {
    $response = $this->withSession([
        MemberSession::KEY => [
            'id' => 'CCL-1002',
            'name' => 'Expired Member',
            'issued_at' => now()->subDays(8)->timestamp,
            'expires_at' => now()->subMinute()->timestamp,
        ],
    ])->get('/about');

    $response
        ->assertRedirect(route('login'))
        ->assertSessionHas('session_expired', MemberSession::EXPIRY_MESSAGE)
        ->assertSessionMissing(MemberSession::KEY);
});

test('expired member sessions can reach the login page again', function () {
    $response = $this->withSession([
        MemberSession::KEY => [
            'id' => 'CCL-1003',
            'name' => 'Expired Member',
            'issued_at' => now()->subDays(8)->timestamp,
            'expires_at' => now()->subMinute()->timestamp,
        ],
    ])->get('/');

    $response
        ->assertOk()
        ->assertSee(MemberSession::EXPIRY_MESSAGE)
        ->assertSessionMissing(MemberSession::KEY);
});
