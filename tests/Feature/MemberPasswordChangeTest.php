<?php

use App\Support\MemberSession;
use Illuminate\Support\Facades\DB;

test('member can change password from profile', function () {
    $lookup = \Mockery::mock();
    $update = \Mockery::mock();

    DB::shouldReceive('table')
        ->once()
        ->with('T_MEMBER')
        ->andReturn($lookup);

    $lookup->shouldReceive('where')
        ->once()
        ->with('tx_org_id', 'CCL-1001')
        ->andReturnSelf();
    $lookup->shouldReceive('where')
        ->once()
        ->with('tx_password', 'old-pass')
        ->andReturnSelf();
    $lookup->shouldReceive('exists')
        ->once()
        ->andReturnTrue();

    DB::shouldReceive('table')
        ->once()
        ->with('T_MEMBER')
        ->andReturn($update);

    $update->shouldReceive('where')
        ->once()
        ->with('tx_org_id', 'CCL-1001')
        ->andReturnSelf();
    $update->shouldReceive('update')
        ->once()
        ->with(['tx_password' => 'new-pass-123'])
        ->andReturn(1);

    $response = $this
        ->withSession([
            MemberSession::KEY => MemberSession::build('CCL-1001', 'Test Member'),
        ])
        ->from(route('profile'))
        ->post(route('profile.password.update'), [
            'current_password' => 'old-pass',
            'new_password' => 'new-pass-123',
            'new_password_confirmation' => 'new-pass-123',
        ]);

    $response
        ->assertRedirect(route('profile'))
        ->assertSessionHas('password_status', 'Password changed successfully.');
});

test('member cannot change password with incorrect current password', function () {
    $lookup = \Mockery::mock();

    DB::shouldReceive('table')
        ->once()
        ->with('T_MEMBER')
        ->andReturn($lookup);

    $lookup->shouldReceive('where')
        ->once()
        ->with('tx_org_id', 'CCL-1002')
        ->andReturnSelf();
    $lookup->shouldReceive('where')
        ->once()
        ->with('tx_password', 'wrong-pass')
        ->andReturnSelf();
    $lookup->shouldReceive('exists')
        ->once()
        ->andReturnFalse();

    $response = $this
        ->withSession([
            MemberSession::KEY => MemberSession::build('CCL-1002', 'Test Member'),
        ])
        ->from(route('profile'))
        ->post(route('profile.password.update'), [
            'current_password' => 'wrong-pass',
            'new_password' => 'new-pass-123',
            'new_password_confirmation' => 'new-pass-123',
        ]);

    $response
        ->assertRedirect(route('profile'))
        ->assertSessionHasErrors([
            'current_password' => 'The current password is incorrect.',
        ]);
});
