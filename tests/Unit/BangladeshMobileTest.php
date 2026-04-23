<?php

use App\Support\BangladeshMobile;

test('it normalizes bangladesh mobile numbers into e164 and candidate variants', function () {
    $normalized = BangladeshMobile::normalize('01711-721053');

    expect($normalized)->not->toBeNull()
        ->and($normalized['e164'])->toBe('+8801711721053')
        ->and($normalized['national'])->toBe('01711721053')
        ->and($normalized['local'])->toBe('1711721053')
        ->and($normalized['candidates'])->toBe([
            '8801711721053',
            '01711721053',
            '1711721053',
        ]);
});

test('it rejects non bangladesh mobile numbers', function () {
    expect(BangladeshMobile::normalize('031654803'))->toBeNull();
});
