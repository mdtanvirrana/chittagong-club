<?php

use App\Services\Auth\RobiSmsService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class);

test('robi sms service sends otp through the mram gateway parameters', function () {
    Http::fake(function ($request) {
        expect($request->url())->toBe('https://msg.example.test/smsapi?api_key=sms-api-key&type=text&contacts=8801711721053&senderid=8809601019288&msg=Test%20OTP');
        expect($request->data()['api_key'] ?? null)->toBe('sms-api-key');
        expect($request->data()['type'] ?? null)->toBe('text');
        expect($request->data()['contacts'] ?? null)->toBe('8801711721053');
        expect($request->data()['senderid'] ?? null)->toBe('8809601019288');
        expect($request->data()['msg'] ?? null)->toBe('Test OTP');

        return Http::response('SMS SUBMITTED: ID - 12345');
    });

    Config::set('services.robi_sms.url', 'https://msg.example.test/smsapi');
    Config::set('services.robi_sms.api_key', 'sms-api-key');
    Config::set('services.robi_sms.type', 'text');
    Config::set('services.robi_sms.sender_id', '8809601019288');

    app(RobiSmsService::class)->sendOtp('01711721053', 'Test OTP');
});

test('robi sms service throws when the gateway returns an xml error payload', function () {
    $errorXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<ArrayOfServiceClass>
    <ServiceClass>
        <MessageId>0</MessageId>
        <Status>-1</Status>
        <StatusText>Error occurred</StatusText>
        <ErrorCode>1504</ErrorCode>
        <ErrorText>Invalid Parameter</ErrorText>
        <SMSCount>0</SMSCount>
        <CurrentCredit>0.0</CurrentCredit>
    </ServiceClass>
</ArrayOfServiceClass>
XML;

    Http::fake(fn () => Http::response($errorXml, 200, ['Content-Type' => 'application/xml']));

    Config::set('services.robi_sms.url', 'https://msg.example.test/smsapi');
    Config::set('services.robi_sms.api_key', 'sms-api-key');
    Config::set('services.robi_sms.type', 'text');
    Config::set('services.robi_sms.sender_id', '8809601019288');

    expect(fn () => app(RobiSmsService::class)->sendOtp('01711721053', 'Test OTP'))
        ->toThrow(\RuntimeException::class, 'Invalid Parameter (code 1504, status -1).');
});
