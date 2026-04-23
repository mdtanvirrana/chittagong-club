<?php

use App\Support\MemberSession;
use Illuminate\Support\Facades\DB;

test('member can sign in with an active customer record and Users_App credentials', function () {
    $memberLookup = \Mockery::mock();
    $credentialLookup = \Mockery::mock();
    $log = \Mockery::mock();

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
    $memberLookup->shouldReceive('select')
        ->once()
        ->with(['c.PrvCusID', 'c.Title', 'c.CusName'])
        ->andReturnSelf();
    $memberLookup->shouldReceive('first')
        ->once()
        ->andReturn((object) [
            'PrvCusID' => 'CCL-1001',
            'Title' => 'Mr.',
            'CusName' => 'Test Member',
        ]);

    DB::shouldReceive('table')
        ->twice()
        ->with('Users_App')
        ->andReturn($credentialLookup);

    $credentialLookup->shouldReceive('where')
        ->twice()
        ->with('PrvcusID', 'CCL-1001')
        ->andReturnSelf();
    $credentialLookup->shouldReceive('value')
        ->twice()
        ->with('Password')
        ->andReturn(md5('secret123'));

    DB::shouldReceive('table')
        ->once()
        ->with('UsersLog')
        ->andReturn($log);

    $log->shouldReceive('insert')
        ->once()
        ->with(\Mockery::on(function (array $values): bool {
            return $values['PrvcusID'] === 'CCL-1001'
                && $values['Status'] === 'Login'
                && isset($values['EDate'], $values['ETime'], $values['eIP']);
        }))
        ->andReturnTrue();

    $response = $this->from(route('login'))->post(route('login.post'), [
        'member_id' => 'CCL-1001',
        'password' => 'secret123',
    ]);

    $response
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas(MemberSession::KEY, fn ($member) => data_get($member, 'id') === 'CCL-1001'
            && data_get($member, 'name') === 'Mr. Test Member');
});

test('member login fails when Users_App credentials do not match', function () {
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
    $memberLookup->shouldReceive('select')
        ->once()
        ->with(['c.PrvCusID', 'c.Title', 'c.CusName'])
        ->andReturnSelf();
    $memberLookup->shouldReceive('first')
        ->once()
        ->andReturn((object) [
            'PrvCusID' => 'CCL-1002',
            'Title' => '',
            'CusName' => 'Test Member',
        ]);

    DB::shouldReceive('table')
        ->twice()
        ->with('Users_App')
        ->andReturn($credentialLookup);

    $credentialLookup->shouldReceive('where')
        ->twice()
        ->with('PrvcusID', 'CCL-1002')
        ->andReturnSelf();
    $credentialLookup->shouldReceive('value')
        ->twice()
        ->with('Password')
        ->andReturn(md5('different-pass'));

    $response = $this->from(route('login'))->post(route('login.post'), [
        'member_id' => 'CCL-1002',
        'password' => 'wrong-pass',
    ]);

    $response
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors([
            'member_id' => 'Invalid Membership ID or password.',
        ]);
});

test('member is redirected to first-time password setup when no password exists', function () {
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
        ->with('c.PrvCusID', 'CCL-1003')
        ->andReturnSelf();
    $memberLookup->shouldReceive('select')
        ->once()
        ->with(['c.PrvCusID', 'c.Title', 'c.CusName'])
        ->andReturnSelf();
    $memberLookup->shouldReceive('first')
        ->once()
        ->andReturn((object) [
            'PrvCusID' => 'CCL-1003',
            'Title' => '',
            'CusName' => 'No Password Member',
        ]);

    DB::shouldReceive('table')
        ->once()
        ->with('Users_App')
        ->andReturn($credentialLookup);

    $credentialLookup->shouldReceive('where')
        ->once()
        ->with('PrvcusID', 'CCL-1003')
        ->andReturnSelf();
    $credentialLookup->shouldReceive('value')
        ->once()
        ->with('Password')
        ->andReturn(null);

    $response = $this->from(route('login'))->post(route('login.post'), [
        'member_id' => 'CCL-1003',
        'password' => 'anything',
    ]);

    $response
        ->assertRedirect(route('password.initial.create'))
        ->assertSessionHas('member_initial_password_setup.member_id', 'CCL-1003')
        ->assertSessionHas('member_initial_password_setup.member_name', 'No Password Member');
});

test('member can create a first-time password and is redirected back to login', function () {
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
        ->with('c.PrvCusID', 'CCL-1003')
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
        ->with('PrvcusID', 'CCL-1003')
        ->andReturnSelf();
    $credentialLookup->shouldReceive('value')
        ->once()
        ->with('Password')
        ->andReturn(null);

    DB::shouldReceive('table')
        ->once()
        ->with('Users_App_Pass')
        ->andReturn($passwordHistory);

    $passwordHistory->shouldReceive('insert')
        ->once()
        ->with(\Mockery::on(function (array $values): bool {
            return $values['PrvcusID'] === 'CCL-1003'
                && $values['NewPass'] === md5('first-pass-123')
                && $values['ConPass'] === md5('first-pass-123')
                && $values['Note'] === 'new'
                && isset($values['EDate'], $values['ETime']);
        }))
        ->andReturnTrue();

    $credentialSave->shouldReceive('updateOrInsert')
        ->once()
        ->with(
            ['PrvcusID' => 'CCL-1003'],
            \Mockery::on(function (array $values): bool {
                return $values['Password'] === md5('first-pass-123')
                    && isset($values['LastUpdateDate'], $values['LastUpdateTime']);
            })
        )
        ->andReturnTrue();

    $response = $this
        ->withSession([
            'member_initial_password_setup' => [
                'member_id' => 'CCL-1003',
                'member_name' => 'No Password Member',
            ],
        ])
        ->post(route('password.initial.store'), [
            'password' => 'first-pass-123',
            'password_confirmation' => 'first-pass-123',
        ]);

    $response
        ->assertRedirect(route('login'))
        ->assertSessionHas('password_reset_status', 'Password created successfully. Sign in with your new password.');
});
