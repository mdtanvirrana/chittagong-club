<?php

use App\Support\MemberSession;
use Illuminate\Support\Facades\DB;

test('member can change password from profile', function () {
    $memberLookup = \Mockery::mock();
    $credentialLookup = \Mockery::mock();
    $passwordHistory = \Mockery::mock();
    $credentialSave = \Mockery::mock();

    DB::shouldReceive('table')
        ->once()
        ->with('CustomerMst as c')
        ->andReturn($memberLookup);

    $memberLookup->shouldReceive('join')
        ->once()
        ->with('CusCardCatagory as cc', 'c.Cardid', '=', 'cc.CardID')
        ->andReturnSelf();
    $memberLookup->shouldReceive('where')
        ->once()
        ->with('cc.GM', 'M')
        ->andReturnSelf();
    $memberLookup->shouldReceive('where')
        ->once()
        ->with('c.MemExpTypeID', 100)
        ->andReturnSelf();
    $memberLookup->shouldReceive('where')
        ->once()
        ->with('c.PrvCusID', 'CCL-1001')
        ->andReturnSelf();
    $memberLookup->shouldReceive('exists')
        ->once()
        ->andReturnTrue();

    DB::shouldReceive('table')
        ->twice()
        ->with('Users_App')
        ->andReturn($credentialLookup, $credentialSave);

    $credentialLookup->shouldReceive('where')
        ->once()
        ->with('PrvcusID', 'CCL-1001')
        ->andReturnSelf();
    $credentialLookup->shouldReceive('value')
        ->once()
        ->with('Password')
        ->andReturn(md5('old-pass'));

    DB::shouldReceive('table')
        ->once()
        ->with('Users_App_Pass')
        ->andReturn($passwordHistory);

    $passwordHistory->shouldReceive('insert')
        ->once()
        ->with(\Mockery::on(function (array $values): bool {
            return $values['PrvcusID'] === 'CCL-1001'
                && $values['NewPass'] === md5('new-pass-123')
                && $values['ConPass'] === md5('new-pass-123')
                && $values['Note'] === 'changed'
                && isset($values['EDate'], $values['ETime']);
        }))
        ->andReturnTrue();

    $credentialSave->shouldReceive('updateOrInsert')
        ->once()
        ->with(
            ['PrvcusID' => 'CCL-1001'],
            \Mockery::on(function (array $values): bool {
                return $values['Password'] === md5('new-pass-123')
                    && isset($values['LastUpdateDate'], $values['LastUpdateTime']);
            })
        )
        ->andReturnTrue();

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
    $memberLookup = \Mockery::mock();
    $credentialLookup = \Mockery::mock();

    DB::shouldReceive('table')
        ->once()
        ->with('CustomerMst as c')
        ->andReturn($memberLookup);

    $memberLookup->shouldReceive('join')
        ->once()
        ->with('CusCardCatagory as cc', 'c.Cardid', '=', 'cc.CardID')
        ->andReturnSelf();
    $memberLookup->shouldReceive('where')
        ->once()
        ->with('cc.GM', 'M')
        ->andReturnSelf();
    $memberLookup->shouldReceive('where')
        ->once()
        ->with('c.MemExpTypeID', 100)
        ->andReturnSelf();
    $memberLookup->shouldReceive('where')
        ->once()
        ->with('c.PrvCusID', 'CCL-1002')
        ->andReturnSelf();
    $memberLookup->shouldReceive('exists')
        ->once()
        ->andReturnTrue();

    DB::shouldReceive('table')
        ->once()
        ->with('Users_App')
        ->andReturn($credentialLookup);

    $credentialLookup->shouldReceive('where')
        ->once()
        ->with('PrvcusID', 'CCL-1002')
        ->andReturnSelf();
    $credentialLookup->shouldReceive('value')
        ->once()
        ->with('Password')
        ->andReturn(md5('real-pass'));

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
