<?php

use App\Support\MemberProfileViewData;

test('member qr value is generated from member name and id', function () {
    expect(MemberProfileViewData::formatMemberQrValue('  Jane   Doe  ', 'CCL-1001'))
        ->toBe('Member Name: Jane Doe; Member ID: CCL-1001');
});
