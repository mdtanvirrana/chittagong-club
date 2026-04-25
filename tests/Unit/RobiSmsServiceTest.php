<?php

use App\Services\Auth\RobiSmsService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class);

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

    Config::set('services.robi_sms.url', 'https://robi.example.test/sms');
    Config::set('services.robi_sms.username', 'sms-user');
    Config::set('services.robi_sms.password', 'sms-pass');
    Config::set('services.robi_sms.from', '8801847170339');

    expect(fn () => app(RobiSmsService::class)->sendOtp('01711721053', 'Test OTP'))
        ->toThrow(\RuntimeException::class, 'Invalid Parameter (code 1504, status -1).');
});
