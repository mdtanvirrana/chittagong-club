<?php

use App\Support\MemberSession;
use Illuminate\Support\Facades\DB;

test('members can access protected routes before the inactivity timeout', function () {
    $response = $this->withSession([
        MemberSession::KEY => MemberSession::build('CCL-1001', 'Test Member'),
    ])->get('/about');

    $response->assertOk();
});

test('active member requests refresh the inactivity timeout', function () {
    $response = $this->withSession([
        MemberSession::KEY => [
            'id' => 'CCL-1001',
            'name' => 'Active Member',
            'issued_at' => now()->subMinutes(4)->timestamp,
            'expires_at' => now()->addSecond()->timestamp,
        ],
    ])->get('/about');

    $response
        ->assertOk()
        ->assertSessionHas(MemberSession::KEY, function (array $member): bool {
            return data_get($member, 'expires_at') > now()->addMinutes(4)->timestamp;
        });
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

test('idle-triggered logout flashes the inactivity expiry message', function () {
    $log = \Mockery::mock();

    DB::shouldReceive('table')
        ->once()
        ->with('UsersLog')
        ->andReturn($log);

    $log->shouldReceive('insert')
        ->once()
        ->with(\Mockery::on(function (array $values): bool {
            return $values['PrvcusID'] === 'CCL-1004'
                && $values['Status'] === 'Logout'
                && $values['eIP'] === '127.0.0.1';
        }))
        ->andReturn(true);

    $response = $this->withSession([
        MemberSession::KEY => MemberSession::build('CCL-1004', 'Idle Member'),
    ])->post(route('logout'), [
        'inactive' => 1,
    ]);

    $response
        ->assertRedirect(route('login'))
        ->assertSessionHas('session_expired', MemberSession::EXPIRY_MESSAGE)
        ->assertSessionMissing(MemberSession::KEY);
});
