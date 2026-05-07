<?php

use App\Support\MemberSession;
use App\Support\PortalCache;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    PortalCache::clearAffiliatedClubCaches();

    Schema::dropIfExists('T_AFFILIATED_CLUBS');
    Schema::dropIfExists('ACountry');

    Schema::create('ACountry', function (Blueprint $table) {
        $table->string('CID')->nullable();
        $table->string('CName')->nullable();
    });

    Schema::create('T_AFFILIATED_CLUBS', function (Blueprint $table) {
        $table->integer('id_affiliated_club_key')->primary();
        $table->integer('id_serial')->nullable();
        $table->boolean('is_active')->default(true);
        $table->string('COMPANY')->nullable();
        $table->string('Country')->nullable();
        $table->string('BranchName')->nullable();
        $table->string('BranchAddress')->nullable();
        $table->string('HOAddress')->nullable();
        $table->string('BranchTel')->nullable();
        $table->string('HOTel')->nullable();
        $table->string('tx_mobile')->nullable();
        $table->string('tx_email')->nullable();
        $table->string('tx_url')->nullable();
        $table->string('tx_fax')->nullable();
        $table->string('CEO')->nullable();
        $table->string('Logo_Path')->nullable();
        $table->string('image_path')->nullable();
    });
});

afterEach(function () {
    PortalCache::clearAffiliatedClubCaches();
});

test('member affiliated clubs are ordered by country with bangladesh first', function () {
    DB::table('ACountry')->insert([
        ['CID' => '88', 'CName' => 'Bangladesh'],
        ['CID' => '91', 'CName' => 'India'],
    ]);

    DB::table('T_AFFILIATED_CLUBS')->insert([
        [
            'id_affiliated_club_key' => 1,
            'id_serial' => 20,
            'is_active' => true,
            'COMPANY' => 'Tollygunge Club Limited',
            'Country' => '91',
            'HOAddress' => 'Kolkata',
        ],
        [
            'id_affiliated_club_key' => 2,
            'id_serial' => 30,
            'is_active' => true,
            'COMPANY' => 'Dhaka Club Limited',
            'Country' => '88',
            'HOAddress' => 'Dhaka',
        ],
        [
            'id_affiliated_club_key' => 3,
            'id_serial' => 10,
            'is_active' => true,
            'COMPANY' => 'Unknown Country Club',
            'Country' => '0',
            'HOAddress' => 'N/A',
        ],
        [
            'id_affiliated_club_key' => 4,
            'id_serial' => 5,
            'is_active' => false,
            'COMPANY' => 'Hidden Bangladesh Club',
            'Country' => '88',
            'HOAddress' => 'Dhaka',
        ],
    ]);

    $response = $this
        ->withSession([
            MemberSession::KEY => MemberSession::build('CCL-1001', 'Test Member'),
        ])
        ->get(route('affiliated-clubs'));

    $response->assertOk();

    $clubs = $response->viewData('clubs')->all();

    expect(array_column($clubs, 'name'))->toBe([
        'Dhaka Club Limited',
        'Tollygunge Club Limited',
        'Unknown Country Club',
    ])->and(array_column($clubs, 'country'))->toBe([
        'Bangladesh',
        'India',
        'Country not set',
    ]);
});
