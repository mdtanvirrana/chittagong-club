<?php

use App\Support\PortalContent;

test('portal content converts plain text to delta and back', function () {
    $text = "Line one\nLine two";
    $payload = PortalContent::plainTextToDelta($text);

    expect(PortalContent::deltaToPlainText($payload))->toBe($text);
});

test('portal content ignores placeholder optional values', function () {
    expect(PortalContent::cleanedOptionalField('?'))->toBeNull()
        ->and(PortalContent::cleanedOptionalField('  '))->toBeNull()
        ->and(PortalContent::cleanedOptionalField('https://example.com'))->toBe('https://example.com');
});
