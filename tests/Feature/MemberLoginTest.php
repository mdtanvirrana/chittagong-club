<?php

use App\Support\MemberSession;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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
        ->with(['c.PrvCusID', 'c.Title', 'c.CusName', 'c.Mobile', 'c.Phone'])
        ->andReturnSelf();
    $memberLookup->shouldReceive('first')
        ->once()
        ->andReturn((object) [
            'PrvCusID' => 'CCL-1001',
            'Title' => 'Mr.',
            'CusName' => 'Test Member',
            'Mobile' => '01711111111',
            'Phone' => null,
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
        ->with(['c.PrvCusID', 'c.Title', 'c.CusName', 'c.Mobile', 'c.Phone'])
        ->andReturnSelf();
    $memberLookup->shouldReceive('first')
        ->once()
        ->andReturn((object) [
            'PrvCusID' => 'CCL-1002',
            'Title' => '',
            'CusName' => 'Test Member',
            'Mobile' => '01712222222',
            'Phone' => null,
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
        ->with(['c.PrvCusID', 'c.Title', 'c.CusName', 'c.Mobile', 'c.Phone'])
        ->andReturnSelf();
    $memberLookup->shouldReceive('first')
        ->once()
        ->andReturn((object) [
            'PrvCusID' => 'CCL-1003',
            'Title' => '',
            'CusName' => 'No Password Member',
            'Mobile' => '01711721053',
            'Phone' => null,
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
        ->assertSessionHas('member_initial_password_setup.member_name', 'No Password Member')
        ->assertSessionHas('member_initial_password_setup.phone.e164_digits', '8801711721053');
});

test('member can create a first-time password through sms otp verification', function () {
    $sendMemberLookup = \Mockery::mock();
    $sendCredentialLookup = \Mockery::mock();
    $profileLookup = \Mockery::mock();
    $otpInsert = \Mockery::mock();
    $otpSendUpdate = \Mockery::mock();
    $otpLookup = \Mockery::mock();
    $otpVerifyUpdate = \Mockery::mock();
    $storeMemberLookup = \Mockery::mock();
    $storeCredentialLookup = \Mockery::mock();
    $passwordHistory = \Mockery::mock();
    $credentialSave = \Mockery::mock();
    $otpUseUpdate = \Mockery::mock();
    $capturedMessage = null;
    $successXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<ArrayOfServiceClass>
    <ServiceClass>
        <MessageId>12345</MessageId>
        <Status>0</Status>
        <StatusText>success</StatusText>
        <ErrorCode>0</ErrorCode>
        <ErrorText></ErrorText>
        <SMSCount>1</SMSCount>
        <CurrentCredit>42.0</CurrentCredit>
    </ServiceClass>
</ArrayOfServiceClass>
XML;

    Http::fake(function ($request) use (&$capturedMessage, $successXml) {
        $capturedMessage = $request->data()['Message'] ?? null;
        expect($request->data()['Username'] ?? null)->toBe('sms-user');
        expect($request->data()['Password'] ?? null)->toBe('sms-pass');
        expect($request->data()['From'] ?? null)->toBe('8801847170339');
        expect($request->data()['To'] ?? null)->toBe('01711721053');
        expect(isset($request->data()['submit']))->toBeFalse();

        return Http::response($successXml, 200, ['Content-Type' => 'application/xml']);
    });

    Config::set('services.robi_sms.url', 'https://robi.example.test/sms');
    Config::set('services.robi_sms.username', 'sms-user');
    Config::set('services.robi_sms.password', 'sms-pass');
    Config::set('services.robi_sms.from', '8801847170339');

    DB::shouldReceive('table')
        ->once()
        ->with('CustomerMst as c')
        ->andReturn($sendMemberLookup);

    $sendMemberLookup->shouldReceive('join')
        ->once()
        ->with('CusCardCatagory as cc', 'c.Cardid', '=', 'cc.CardID')
        ->andReturnSelf();
    $sendMemberLookup->shouldReceive('where')
        ->once()
        ->with('cc.GM', 'M')
        ->andReturnSelf();
    $sendMemberLookup->shouldReceive('where')
        ->once()
        ->with('c.MemExpTypeID', 100)
        ->andReturnSelf();
    $sendMemberLookup->shouldReceive('where')
        ->once()
        ->with('c.PrvCusID', 'CCL-1003')
        ->andReturnSelf();
    $sendMemberLookup->shouldReceive('exists')
        ->once()
        ->andReturnTrue();

    DB::shouldReceive('table')
        ->once()
        ->with('Users_App')
        ->andReturn($sendCredentialLookup);

    $sendCredentialLookup->shouldReceive('where')
        ->once()
        ->with('PrvcusID', 'CCL-1003')
        ->andReturnSelf();
    $sendCredentialLookup->shouldReceive('value')
        ->once()
        ->with('Password')
        ->andReturn(null);

    DB::shouldReceive('table')
        ->once()
        ->with('CPROFILE')
        ->andReturn($profileLookup);

    $profileLookup->shouldReceive('first')
        ->once()
        ->andReturn((object) [
            'BranchName' => 'CCLApps',
            'CompanyName' => 'Chittagong Club Ltd.',
        ]);

    DB::shouldReceive('table')
        ->once()
        ->with('SMSSend_OTP')
        ->andReturn($otpInsert);

    $otpInsert->shouldReceive('insertGetId')
        ->once()
        ->with(\Mockery::on(function (array $values): bool {
            return $values['PrvcusID'] === 'CCL-1003'
                && $values['Mobile'] === '8801711721053'
                && is_int($values['OTP'])
                && preg_match('/^CCLApps login OTP is \d{6}, Valid for 5 minutes\. Chittagong Club Ltd\.$/', $values['SMSText']) === 1
                && $values['Status'] === 'PENDING'
                && $values['Note'] === 'new'
                && isset($values['SDate'], $values['STime'], $values['EDate'], $values['ETime']);
        }))
        ->andReturn(701);

    DB::shouldReceive('table')
        ->once()
        ->with('SMSSend_OTP')
        ->andReturn($otpSendUpdate);

    $otpSendUpdate->shouldReceive('where')
        ->once()
        ->with('id_otp', 701)
        ->andReturnSelf();
    $otpSendUpdate->shouldReceive('update')
        ->once()
        ->with(\Mockery::on(function (array $values): bool {
            return $values['Status'] === 'SENT'
                && isset($values['EDate'], $values['ETime']);
        }))
        ->andReturn(1);

    $sendResponse = $this
        ->withSession([
            'member_initial_password_setup' => [
                'member_id' => 'CCL-1003',
                'member_name' => 'No Password Member',
                'phone' => [
                    'national' => '01711721053',
                    'e164' => '+8801711721053',
                    'e164_digits' => '8801711721053',
                    'masked' => '+880 17*****053',
                ],
                'otp_id' => null,
                'sent_at' => null,
                'expires_at' => null,
                'attempts' => 0,
                'verified_at' => null,
                'verified_until' => null,
            ],
        ])
        ->post(route('password.initial.send'));

    preg_match('/(\d{6})/', (string) $capturedMessage, $matches);
    $otp = $matches[1] ?? null;

    expect($otp)->not->toBeNull();

    $sendResponse
        ->assertRedirect(route('password.initial.create'))
        ->assertSessionHas('member_initial_password_setup.otp_id', 701)
        ->assertSessionHas('password_setup_status', 'We sent a 6-digit code to +880 17*****053.');

    DB::shouldReceive('table')
        ->once()
        ->with('SMSSend_OTP')
        ->andReturn($otpLookup);

    $otpLookup->shouldReceive('where')
        ->once()
        ->with('id_otp', 701)
        ->andReturnSelf();
    $otpLookup->shouldReceive('first')
        ->once()
        ->andReturn((object) [
            'id_otp' => 701,
            'PrvcusID' => 'CCL-1003',
            'Mobile' => '8801711721053',
            'OTP' => (int) $otp,
            'Status' => 'SENT',
            'Note' => 'new',
        ]);

    DB::shouldReceive('table')
        ->once()
        ->with('SMSSend_OTP')
        ->andReturn($otpVerifyUpdate);

    $otpVerifyUpdate->shouldReceive('where')
        ->once()
        ->with('id_otp', 701)
        ->andReturnSelf();
    $otpVerifyUpdate->shouldReceive('update')
        ->once()
        ->with(\Mockery::on(function (array $values): bool {
            return $values['Status'] === 'VERIFIED'
                && isset($values['EDate'], $values['ETime']);
        }))
        ->andReturn(1);

    $verifyResponse = $this
        ->withSession(app('session.store')->all())
        ->post(route('password.initial.verify.store'), [
            'code' => $otp,
        ]);

    $verifyResponse
        ->assertRedirect(route('password.initial.create'))
        ->assertSessionHas('password_setup_status', 'OTP confirmed. Set your new password now.');

    DB::shouldReceive('table')
        ->once()
        ->with('CustomerMst as c')
        ->andReturn($storeMemberLookup);

    $storeMemberLookup->shouldReceive('join')
        ->once()
        ->with('CusCardCatagory as cc', 'c.Cardid', '=', 'cc.CardID')
        ->andReturnSelf();
    $storeMemberLookup->shouldReceive('where')
        ->once()
        ->with('cc.GM', 'M')
        ->andReturnSelf();
    $storeMemberLookup->shouldReceive('where')
        ->once()
        ->with('c.MemExpTypeID', 100)
        ->andReturnSelf();
    $storeMemberLookup->shouldReceive('where')
        ->once()
        ->with('c.PrvCusID', 'CCL-1003')
        ->andReturnSelf();
    $storeMemberLookup->shouldReceive('exists')
        ->once()
        ->andReturnTrue();

    DB::shouldReceive('table')
        ->once()
        ->with('Users_App')
        ->andReturn($storeCredentialLookup);

    $storeCredentialLookup->shouldReceive('where')
        ->once()
        ->with('PrvcusID', 'CCL-1003')
        ->andReturnSelf();
    $storeCredentialLookup->shouldReceive('value')
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

    DB::shouldReceive('table')
        ->once()
        ->with('Users_App')
        ->andReturn($credentialSave);

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

    DB::shouldReceive('table')
        ->once()
        ->with('SMSSend_OTP')
        ->andReturn($otpUseUpdate);

    $otpUseUpdate->shouldReceive('where')
        ->once()
        ->with('id_otp', 701)
        ->andReturnSelf();
    $otpUseUpdate->shouldReceive('update')
        ->once()
        ->with(\Mockery::on(function (array $values): bool {
            return $values['Status'] === 'USED'
                && isset($values['EDate'], $values['ETime']);
        }))
        ->andReturn(1);

    $response = $this
        ->withSession(app('session.store')->all())
        ->post(route('password.initial.store'), [
            'password' => 'first-pass-123',
            'password_confirmation' => 'first-pass-123',
        ]);

    $response
        ->assertRedirect(route('login'))
        ->assertSessionHas('password_reset_status', 'Password created successfully. Sign in with your new password.');
});
