<?php

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

test('member can reset password with sms otp flow', function () {
    $lookup = \Mockery::mock();
    $update = \Mockery::mock();
    $capturedMessage = null;

    Http::fake(function ($request) use (&$capturedMessage) {
        $capturedMessage = data_get($request->data(), 'message');

        return Http::response(['status' => 'queued'], 200);
    });

    Config::set('services.robi_sms.url', 'https://robi.example.test/sms');
    Config::set('services.robi_sms.token', 'Bearer test-token');

    DB::shouldReceive('raw')
        ->twice()
        ->andReturnUsing(fn (string $value) => new Expression($value));

    DB::shouldReceive('table')
        ->once()
        ->with('T_MEMBER as t')
        ->andReturn($lookup);

    $lookup->shouldReceive('join')
        ->once()
        ->with('CustomerMst as c', 'c.PrvCusID', '=', 't.tx_org_id')
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
                'contact_name' => 'Test Member',
            ],
        ]));

    $sendResponse = $this->post(route('password.forgot.send'), [
        'phone' => '01711721053',
    ]);

    preg_match('/(\d{6})/', (string) $capturedMessage, $matches);
    $otp = $matches[1] ?? null;

    expect($otp)->not->toBeNull();

    $sendResponse
        ->assertRedirect(route('password.forgot.verify'))
        ->assertSessionHas('member_password_reset.phone.e164', '+8801711721053');

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
        ->with('T_MEMBER')
        ->andReturn($update);

    $update->shouldReceive('where')
        ->once()
        ->with('tx_org_id', 'A-0005')
        ->andReturnSelf();
    $update->shouldReceive('update')
        ->once()
        ->with(['tx_password' => 'new-pass-123'])
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

    Http::fake(fn () => Http::response(['status' => 'queued'], 200));

    Config::set('services.robi_sms.url', 'https://robi.example.test/sms');
    Config::set('services.robi_sms.token', 'Bearer test-token');

    DB::shouldReceive('raw')
        ->twice()
        ->andReturnUsing(fn (string $value) => new Expression($value));

    DB::shouldReceive('table')
        ->once()
        ->with('T_MEMBER as t')
        ->andReturn($lookup);

    $lookup->shouldReceive('join')
        ->once()
        ->with('CustomerMst as c', 'c.PrvCusID', '=', 't.tx_org_id')
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
                'contact_name' => 'Test Member',
            ],
        ]));

    $this->post(route('password.forgot.send'), [
        'phone' => '01711721053',
    ])->assertRedirect(route('password.forgot.verify'));

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
