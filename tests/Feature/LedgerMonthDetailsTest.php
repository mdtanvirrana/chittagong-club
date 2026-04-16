<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    Schema::create('List_Department', function (Blueprint $table) {
        $table->integer('Departmentid')->primary();
        $table->string('Departmentname');
    });

    Schema::create('Customer_Ledger', function (Blueprint $table) {
        $table->id();
        $table->string('PrvCusID');
        $table->string('InvMRN')->nullable();
        $table->decimal('DrAmt', 12, 2)->nullable();
        $table->decimal('CrAmt', 12, 2)->nullable();
        $table->dateTime('EDate');
        $table->string('Remarks')->nullable();
        $table->text('Note')->nullable();
        $table->unsignedInteger('DepartmentID');
    });

    Schema::create('SMS_MonthlyBill', function (Blueprint $table) {
        $table->id();
        $table->string('Prvcusid');
        $table->decimal('MBill', 12, 2)->nullable();
        $table->decimal('Bal', 12, 2)->nullable();
        $table->dateTime('sMonth');
    });
});

test('month details includes note for department-wise breakdown entries', function () {
    DB::table('List_Department')->insert([
        'Departmentid' => 7,
        'Departmentname' => 'Restaurant',
    ]);

    DB::table('Customer_Ledger')->insert([
        'PrvCusID' => 'M-100',
        'InvMRN' => 'INV-1001',
        'DrAmt' => 1250,
        'CrAmt' => 0,
        'EDate' => '2026-04-15 10:30:00',
        'Remarks' => 'Lunch',
        'Note' => 'Guest lunch for board meeting',
        'DepartmentID' => 7,
    ]);

    $response = $this
        ->withSession(['member' => ['id' => 'M-100', 'name' => 'Test Member']])
        ->getJson(route('ledger.month-details', ['month' => '2026-04']));

    $response
        ->assertOk()
        ->assertJsonPath('month_key', '2026-04')
        ->assertJsonPath('depts.0.dept', 'Restaurant')
        ->assertJsonPath('depts.0.entries.0.InvMRN', 'INV-1001')
        ->assertJsonPath('depts.0.entries.0.Note', 'Guest lunch for board meeting');
});
