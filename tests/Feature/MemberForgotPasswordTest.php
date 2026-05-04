<?php

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

test('member can reset password with sms otp flow', function () {
    $lookup = \Mockery::mock();
    $profileLookup = \Mockery::mock();
    $otpInsert = \Mockery::mock();
    $otpSendUpdate = \Mockery::mock();
    $otpLookup = \Mockery::mock();
    $otpVerifyUpdate = \Mockery::mock();
    $otpUseUpdate = \Mockery::mock();
    $passwordHistory = \Mockery::mock();
    $credentialSave = \Mockery::mock();
    $capturedMessage = null;
    $successBody = 'SMS SUBMITTED: ID - 12345';

    Http::fake(function ($request) use (&$capturedMessage, $successBody) {
        $capturedMessage = $request->data()['msg'] ?? null;
        expect($request->data()['api_key'] ?? null)->toBe('sms-api-key');
        expect($request->data()['type'] ?? null)->toBe('text');
        expect($request->data()['senderid'] ?? null)->toBe('8809601019288');
        expect($request->data()['contacts'] ?? null)->toBe('8801711721053');
        expect(isset($request->data()['submit']))->toBeFalse();

        return Http::response($successBody);
    });

    Config::set('services.robi_sms.url', 'https://msg.example.test/smsapi');
    Config::set('services.robi_sms.api_key', 'sms-api-key');
    Config::set('services.robi_sms.type', 'text');
    Config::set('services.robi_sms.sender_id', '8809601019288');

    DB::shouldReceive('raw')
        ->twice()
        ->andReturnUsing(fn (string $value) => new Expression($value));

    DB::shouldReceive('table')
        ->once()
        ->with('CustomerMst as c')
        ->andReturn($lookup);

    $lookup->shouldReceive('join')
        ->once()
        ->with('CusCardCatagory as cc', 'c.Cardid', '=', 'cc.CardID')
        ->andReturnSelf();
    $lookup->shouldReceive('where')
        ->once()
        ->with('cc.GM', 'M')
        ->andReturnSelf();
    $lookup->shouldReceive('where')
        ->once()
        ->with('c.MemExpTypeID', 100)
        ->andReturnSelf();
    $lookup->shouldReceive('select')
        ->once()
        ->with(\Mockery::type('array'))
        ->andReturnSelf();
    $lookup->shouldReceive('where')
        ->once()
        ->with(\Mockery::type('Closure'))
        ->andReturnSelf();
    $lookup->shouldReceive('get')
        ->once()
        ->andReturn(collect([
            (object) [
                'member_id' => 'A-0005',
                'member_name' => 'Test Member',
            ],
        ]));

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
            return $values['PrvcusID'] === 'A-0005'
                && $values['Mobile'] === '8801711721053'
                && is_int($values['OTP'])
                && preg_match('/^CCLApps login OTP is \d{6}, Valid for 5 minutes\. Chittagong Club Ltd\.$/', $values['SMSText']) === 1
                && $values['Status'] === 'PENDING'
                && $values['Note'] === 'forget'
                && isset($values['SDate'], $values['STime'], $values['EDate'], $values['ETime']);
        }))
        ->andReturn(501);

    DB::shouldReceive('table')
        ->once()
        ->with('SMSSend_OTP')
        ->andReturn($otpSendUpdate);

    $otpSendUpdate->shouldReceive('where')
        ->once()
        ->with('id_otp', 501)
        ->andReturnSelf();
    $otpSendUpdate->shouldReceive('update')
        ->once()
        ->with(\Mockery::on(function (array $values): bool {
            return $values['Status'] === 'SENT'
                && isset($values['EDate'], $values['ETime']);
        }))
        ->andReturn(1);

    $sendResponse = $this->post(route('password.forgot.send'), [
        'phone' => '01711721053',
    ]);

    preg_match('/(\d{6})/', (string) $capturedMessage, $matches);
    $otp = $matches[1] ?? null;

    expect($otp)->not->toBeNull();

    $sendResponse
        ->assertRedirect(route('password.forgot.verify'))
        ->assertSessionHas('member_password_reset.phone.e164', '+8801711721053');

    DB::shouldReceive('table')
        ->once()
        ->with('SMSSend_OTP')
        ->andReturn($otpLookup);

    $otpLookup->shouldReceive('where')
        ->once()
        ->with('id_otp', 501)
        ->andReturnSelf();
    $otpLookup->shouldReceive('first')
        ->once()
        ->andReturn((object) [
            'id_otp' => 501,
            'PrvcusID' => 'A-0005',
            'Mobile' => '8801711721053',
            'OTP' => (int) $otp,
            'Status' => 'SENT',
            'Note' => 'forget',
        ]);

    DB::shouldReceive('table')
        ->once()
        ->with('SMSSend_OTP')
        ->andReturn($otpVerifyUpdate);

    $otpVerifyUpdate->shouldReceive('where')
        ->once()
        ->with('id_otp', 501)
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
        ->post(route('password.forgot.verify.store'), [
            'code' => $otp,
        ]);

    $verifyResponse
        ->assertRedirect(route('password.forgot.reset'))
        ->assertSessionHas('password_reset_status', 'OTP confirmed. Set a new password now.');

    DB::shouldReceive('table')
        ->once()
        ->with('Users_App_Pass')
        ->andReturn($passwordHistory);

    $passwordHistory->shouldReceive('insert')
        ->once()
        ->with(\Mockery::on(function (array $values): bool {
            return $values['PrvcusID'] === 'A-0005'
                && $values['NewPass'] === md5('new-pass-123')
                && $values['ConPass'] === md5('new-pass-123')
                && $values['Note'] === 'forget'
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
            ['PrvcusID' => 'A-0005'],
            \Mockery::on(function (array $values): bool {
                return $values['Password'] === md5('new-pass-123')
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
        ->with('id_otp', 501)
        ->andReturnSelf();
    $otpUseUpdate->shouldReceive('update')
        ->once()
        ->with(\Mockery::on(function (array $values): bool {
            return $values['Status'] === 'USED'
                && isset($values['EDate'], $values['ETime']);
        }))
        ->andReturn(1);

    $resetResponse = $this
        ->withSession(app('session.store')->all())
        ->post(route('password.forgot.update'), [
            'password' => 'new-pass-123',
            'password_confirmation' => 'new-pass-123',
        ]);

    $resetResponse
        ->assertRedirect(route('login'))
        ->assertSessionHas('password_reset_status', 'Password updated successfully. Sign in with your new password.');
});

test('member sees validation error for an invalid otp', function () {
    $lookup = \Mockery::mock();
    $profileLookup = \Mockery::mock();
    $otpInsert = \Mockery::mock();
    $otpSendUpdate = \Mockery::mock();
    $otpLookup = \Mockery::mock();
    $successBody = 'SMS SUBMITTED: ID - 12345';

    Http::fake(function ($request) use ($successBody) {
        expect($request->data()['api_key'] ?? null)->toBe('sms-api-key');
        expect($request->data()['type'] ?? null)->toBe('text');
        expect($request->data()['senderid'] ?? null)->toBe('8809601019288');
        expect($request->data()['contacts'] ?? null)->toBe('8801711721053');
        expect(isset($request->data()['submit']))->toBeFalse();

        return Http::response($successBody);
    });

    Config::set('services.robi_sms.url', 'https://msg.example.test/smsapi');
    Config::set('services.robi_sms.api_key', 'sms-api-key');
    Config::set('services.robi_sms.type', 'text');
    Config::set('services.robi_sms.sender_id', '8809601019288');

    DB::shouldReceive('raw')
        ->twice()
        ->andReturnUsing(fn (string $value) => new Expression($value));

    DB::shouldReceive('table')
        ->once()
        ->with('CustomerMst as c')
        ->andReturn($lookup);

    $lookup->shouldReceive('join')
        ->once()
        ->with('CusCardCatagory as cc', 'c.Cardid', '=', 'cc.CardID')
        ->andReturnSelf();
    $lookup->shouldReceive('where')
        ->once()
        ->with('cc.GM', 'M')
        ->andReturnSelf();
    $lookup->shouldReceive('where')
        ->once()
        ->with('c.MemExpTypeID', 100)
        ->andReturnSelf();
    $lookup->shouldReceive('select')
        ->once()
        ->with(\Mockery::type('array'))
        ->andReturnSelf();
    $lookup->shouldReceive('where')
        ->once()
        ->with(\Mockery::type('Closure'))
        ->andReturnSelf();
    $lookup->shouldReceive('get')
        ->once()
        ->andReturn(collect([
            (object) [
                'member_id' => 'A-0005',
                'member_name' => 'Test Member',
            ],
        ]));

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
        ->with(\Mockery::type('array'))
        ->andReturn(601);

    DB::shouldReceive('table')
        ->once()
        ->with('SMSSend_OTP')
        ->andReturn($otpSendUpdate);

    $otpSendUpdate->shouldReceive('where')
        ->once()
        ->with('id_otp', 601)
        ->andReturnSelf();
    $otpSendUpdate->shouldReceive('update')
        ->once()
        ->with(\Mockery::on(function (array $values): bool {
            return $values['Status'] === 'SENT'
                && isset($values['EDate'], $values['ETime']);
        }))
        ->andReturn(1);

    $this->post(route('password.forgot.send'), [
        'phone' => '01711721053',
    ])->assertRedirect(route('password.forgot.verify'));

    DB::shouldReceive('table')
        ->once()
        ->with('SMSSend_OTP')
        ->andReturn($otpLookup);

    $otpLookup->shouldReceive('where')
        ->once()
        ->with('id_otp', 601)
        ->andReturnSelf();
    $otpLookup->shouldReceive('first')
        ->once()
        ->andReturn((object) [
            'id_otp' => 601,
            'PrvcusID' => 'A-0005',
            'Mobile' => '8801711721053',
            'OTP' => 123456,
            'Status' => 'SENT',
            'Note' => 'forget',
        ]);

    $response = $this
        ->withSession(app('session.store')->all())
        ->from(route('password.forgot.verify'))
        ->post(route('password.forgot.verify.store'), [
            'code' => '000000',
        ]);

    $response
        ->assertRedirect(route('password.forgot.verify'))
        ->assertSessionHasErrors([
            'code' => 'The OTP is invalid. Please check the SMS and try again.',
        ]);
});
