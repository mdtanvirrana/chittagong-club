<?php

use App\Support\MemberSession;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

test('logging out clears member-specific cached data', function () {
    $memberId = 'CCL-LOGOUT-CACHE';
    $store = \Mockery::mock(Repository::class);
    $log = \Mockery::mock();

    DB::shouldReceive('table')
        ->once()
        ->with('UsersLog')
        ->andReturn($log);

    $log->shouldReceive('insert')
        ->once()
        ->with(\Mockery::on(function (array $values) use ($memberId): bool {
            return $values['PrvcusID'] === $memberId
                && $values['Status'] === 'Logout'
                && isset($values['EDate'], $values['ETime'], $values['eIP']);
        }))
        ->andReturnTrue();

    Cache::shouldReceive('store')
        ->once()
        ->with('file')
        ->andReturn($store);

    foreach ([
        "dashboard_member_{$memberId}_v2",
        "dashboard_member_{$memberId}_stale_v2",
        "dashboard_ledger_totals_{$memberId}_v2",
        "dashboard_ledger_totals_{$memberId}_stale_v2",
        "dashboard_member_credit_{$memberId}_v1",
        "dashboard_member_credit_{$memberId}_stale_v1",
        "member_profile_view_{$memberId}_v1",
        "member_profile_view_{$memberId}_v2",
    ] as $key) {
        $store->shouldReceive('forget')
            ->once()
            ->with($key)
            ->andReturnTrue();
    }

    $response = $this->withSession([
        MemberSession::KEY => MemberSession::build($memberId, 'Cache Test Member'),
    ])->post('/logout');

    $response->assertRedirect(route('login'));
});
