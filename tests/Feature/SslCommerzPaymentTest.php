<?php

use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('services.sslcommerz.store_id', 'testbox');
    config()->set('services.sslcommerz.store_password', 'qwerty');
    config()->set('services.sslcommerz.sandbox', true);
});

test('member can initiate an sslcommerz payment', function () {
    Http::fake([
        'https://sandbox.sslcommerz.com/gwprocess/v4/api.php' => Http::response([
            'status' => 'SUCCESS',
            'sessionkey' => 'session-key-123',
            'GatewayPageURL' => 'https://sandbox.sslcommerz.com/EasyCheckOut/test',
        ], 200),
    ]);

    $response = $this
        ->withSession(['member' => ['id' => 'M-100', 'name' => 'Test Member']])
        ->postJson(route('ledger.payments.sslcommerz.initiate'), [
            'amount' => '1500.00',
            'note' => 'Ledger due payment',
            'accept_terms' => true,
        ]);

    $response
        ->assertOk()
        ->assertJson([
            'message' => 'Payment initiated.',
            'gateway_url' => 'https://sandbox.sslcommerz.com/EasyCheckOut/test',
        ]);

    $transaction = PaymentTransaction::first();

    expect($transaction)->not->toBeNull()
        ->and($transaction->member_id)->toBe('M-100')
        ->and($transaction->status)->toBe('pending')
        ->and($transaction->note)->toBe('Ledger due payment')
        ->and($transaction->session_key)->toBe('session-key-123');

    Http::assertSent(fn ($request) => $request->url() === 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php'
        && $request['success_url'] === route('payments.sslcommerz.success')
        && $request['fail_url'] === route('payments.sslcommerz.fail')
        && $request['cancel_url'] === route('payments.sslcommerz.cancel')
        && $request['ipn_url'] === route('payments.sslcommerz.ipn'));
});

test('configured sslcommerz callback urls are sent during initiation', function () {
    config()->set('services.sslcommerz.success_url', 'https://cclapps.com/payments/sslcommerz/success');
    config()->set('services.sslcommerz.fail_url', 'https://cclapps.com/payments/sslcommerz/fail');
    config()->set('services.sslcommerz.cancel_url', 'https://cclapps.com/payments/sslcommerz/cancel');
    config()->set('services.sslcommerz.ipn_url', 'https://cclapps.com/payments/sslcommerz/ipn');

    Http::fake([
        'https://sandbox.sslcommerz.com/gwprocess/v4/api.php' => Http::response([
            'status' => 'SUCCESS',
            'sessionkey' => 'session-key-123',
            'GatewayPageURL' => 'https://sandbox.sslcommerz.com/EasyCheckOut/test',
        ], 200),
    ]);

    $this
        ->withSession(['member' => ['id' => 'M-100', 'name' => 'Test Member']])
        ->postJson(route('ledger.payments.sslcommerz.initiate'), [
            'amount' => '1500.00',
            'note' => 'Ledger due payment',
            'accept_terms' => true,
        ])
        ->assertOk();

    Http::assertSent(fn ($request) => $request['success_url'] === 'https://cclapps.com/payments/sslcommerz/success'
        && $request['fail_url'] === 'https://cclapps.com/payments/sslcommerz/fail'
        && $request['cancel_url'] === 'https://cclapps.com/payments/sslcommerz/cancel'
        && $request['ipn_url'] === 'https://cclapps.com/payments/sslcommerz/ipn');
});

test('member must accept terms before initiating an sslcommerz payment', function () {
    $response = $this
        ->withSession(['member' => ['id' => 'M-100', 'name' => 'Test Member']])
        ->postJson(route('ledger.payments.sslcommerz.initiate'), [
            'amount' => '1500.00',
            'note' => 'Ledger due payment',
        ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['accept_terms']);

    expect(PaymentTransaction::count())->toBe(0);
});

test('successful callback validates payment and stores it in success table', function () {
    $transaction = PaymentTransaction::create([
        'transaction_id' => 'CCL-M100-REF001',
        'member_id' => 'M-100',
        'member_name' => 'Test Member',
        'amount' => 1500,
        'currency' => 'BDT',
        'status' => 'pending',
        'note' => 'April ledger due',
    ]);

    Http::fake([
        'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
            'status' => 'VALIDATED',
            'tran_id' => 'CCL-M100-REF001',
            'val_id' => 'VAL123456',
            'amount' => '1500.00',
            'currency' => 'BDT',
            'bank_tran_id' => 'BANK123',
            'card_type' => 'BKASH-BKASH',
            'store_amount' => '1470.00',
            'tran_date' => '2026-04-15 14:05:00',
            'risk_level' => '0',
        ], 200),
    ]);

    $response = $this->post(route('payments.sslcommerz.success'), [
        'tran_id' => 'CCL-M100-REF001',
        'val_id' => 'VAL123456',
        'status' => 'VALID',
        'bank_tran_id' => 'BANK123',
        'card_type' => 'BKASH-BKASH',
    ]);

    $response->assertRedirect(route('ledger', [
        'payment' => 'success',
        'tran_id' => 'CCL-M100-REF001',
    ], false));

    expect($transaction->fresh()->status)->toBe('success')
        ->and($transaction->fresh()->validation_id)->toBe('VAL123456')
        ->and($transaction->fresh()->bank_transaction_id)->toBe('BANK123');

    $this->assertDatabaseHas('successful_payment_transactions', [
        'payment_transaction_id' => $transaction->id,
        'transaction_id' => 'CCL-M100-REF001',
        'member_id' => 'M-100',
    ]);
});

test('high risk payment is held for manual review after validation', function () {
    $transaction = PaymentTransaction::create([
        'transaction_id' => 'CCL-M100-REF002',
        'member_id' => 'M-100',
        'member_name' => 'Test Member',
        'amount' => 1500,
        'currency' => 'BDT',
        'status' => 'pending',
        'note' => 'April ledger due',
    ]);

    Http::fake([
        'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
            'status' => 'VALIDATED',
            'tran_id' => 'CCL-M100-REF002',
            'val_id' => 'VAL654321',
            'amount' => '1500.00',
            'currency' => 'BDT',
            'bank_tran_id' => 'BANK456',
            'card_type' => 'VISA',
            'store_amount' => '1470.00',
            'tran_date' => '2026-04-15 14:05:00',
            'risk_level' => '1',
            'risk_title' => 'High Risk',
        ], 200),
    ]);

    $response = $this->post(route('payments.sslcommerz.success'), [
        'tran_id' => 'CCL-M100-REF002',
        'val_id' => 'VAL654321',
        'status' => 'VALID',
        'bank_tran_id' => 'BANK456',
        'card_type' => 'VISA',
    ]);

    $response->assertRedirect(route('ledger', [
        'payment' => 'held',
        'tran_id' => 'CCL-M100-REF002',
    ], false));

    expect($transaction->fresh()->status)->toBe('held')
        ->and($transaction->fresh()->validation_id)->toBe('VAL654321')
        ->and($transaction->fresh()->bank_transaction_id)->toBe('BANK456');

    $this->assertDatabaseMissing('successful_payment_transactions', [
        'payment_transaction_id' => $transaction->id,
    ]);
});
