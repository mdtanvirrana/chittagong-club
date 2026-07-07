<?php

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow('2026-04-16 12:00:00');

    Schema::create('CustomerMst', function (Blueprint $table) {
        $table->id();
        $table->string('PrvCusID');
        $table->unsignedInteger('Cardid')->nullable();
        $table->unsignedInteger('MemExpTypeID')->nullable();
        $table->decimal('CreditAmt', 12, 2)->nullable();
    });

    Schema::create('CusCardCatagory', function (Blueprint $table) {
        $table->id();
        $table->unsignedInteger('CardID');
        $table->string('GM')->nullable();
    });

    Schema::create('List_Department', function (Blueprint $table) {
        $table->integer('Departmentid')->primary();
        $table->string('Departmentname');
    });

    Schema::create('Customer_Ledger', function (Blueprint $table) {
        $table->id();
        $table->string('PrvCusID');
        $table->string('InvMRN')->nullable();
        $table->string('ACode')->nullable();
        $table->decimal('DrAmt', 12, 2)->nullable();
        $table->decimal('CrAmt', 12, 2)->nullable();
        $table->dateTime('EDate');
        $table->string('Remarks')->nullable();
        $table->text('Note')->nullable();
        $table->unsignedInteger('DepartmentID');
    });
});

afterEach(function () {
    Carbon::setTestNow();
});

test('ledger overview shows credit-only departments without affecting debit total', function () {
    DB::table('CustomerMst')->insert([
        'PrvCusID' => 'M-100',
        'Cardid' => 1,
        'MemExpTypeID' => 100,
        'CreditAmt' => 50000,
    ]);

    DB::table('CusCardCatagory')->insert([
        'CardID' => 1,
        'GM' => 'M',
    ]);

    DB::table('List_Department')->insert([
        ['Departmentid' => 1, 'Departmentname' => 'Accounts'],
        ['Departmentid' => 2, 'Departmentname' => 'Restaurant'],
    ]);

    DB::table('Customer_Ledger')->insert([
        [
            'PrvCusID' => 'M-100',
            'InvMRN' => 'INV-1001',
            'ACode' => 'Sales',
            'DrAmt' => 5000,
            'CrAmt' => 0,
            'EDate' => '2026-04-10 13:15:00',
            'Remarks' => 'Restaurant bill',
            'Note' => 'Lunch charge',
            'DepartmentID' => 2,
        ],
        [
            'PrvCusID' => 'M-100',
            'InvMRN' => 'INV-1002',
            'ACode' => 'Receipt',
            'DrAmt' => 0,
            'CrAmt' => 10000,
            'EDate' => '2026-04-12 10:00:00',
            'Remarks' => 'Credit received',
            'Note' => 'Adjustment',
            'DepartmentID' => 1,
        ],
    ]);

    $response = $this
        ->withSession(['member' => ['id' => 'M-100', 'name' => 'Test Member']])
        ->getJson(route('ledger.data'));

    $response->assertOk();

    $payload = $response->json();
    $accounts = collect($payload['deptBreakdown'])->firstWhere('dept', 'Accounts');
    $restaurant = collect($payload['deptBreakdown'])->firstWhere('dept', 'Restaurant');

    expect($payload['thisMonthDebit'])->toBe(5000.0)
        ->and($payload['thisMonthCredit'])->toBe(10000.0)
        ->and($accounts)->not->toBeNull()
        ->and($accounts['debit_amount'])->toBe(0.0)
        ->and($accounts['credit_amount'])->toBe(10000.0)
        ->and($restaurant['debit_amount'])->toBe(5000.0)
        ->and($restaurant['credit_amount'])->toBe(0.0);
});
