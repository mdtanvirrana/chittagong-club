<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\InitiateSslCommerzPaymentRequest;
use App\Models\PaymentTransaction;
use App\Models\SuccessfulPaymentTransaction;
use App\Services\Payments\SSLCommerzService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class SSLCommerzPaymentController extends Controller
{
    public function __construct(private readonly SSLCommerzService $sslCommerz) {}

    public function initiate(InitiateSslCommerzPaymentRequest $request): JsonResponse
    {
        $memberId = (string) data_get(session('member'), 'id');

        if ($memberId === '') {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        try {
            $this->sslCommerz->ensureConfigured();
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $member = $this->getMemberContact($memberId);
        $transactionId = $this->makeTransactionId($memberId);

        $transaction = PaymentTransaction::create([
            'transaction_id' => $transactionId,
            'member_id' => $memberId,
            'member_name' => data_get(session('member'), 'name'),
            'amount' => round((float) $request->input('amount'), 2),
            'currency' => $this->sslCommerz->currency(),
            'status' => 'initiated',
            'note' => $request->input('note'),
            'last_status_at' => now(),
        ]);

        try {
            $response = $this->sslCommerz->initiate([
                'total_amount' => number_format((float) $transaction->amount, 2, '.', ''),
                'currency' => $transaction->currency,
                'tran_id' => $transaction->transaction_id,
                'success_url' => route('payments.sslcommerz.success'),
                'fail_url' => route('payments.sslcommerz.fail'),
                'cancel_url' => route('payments.sslcommerz.cancel'),
                'ipn_url' => route('payments.sslcommerz.ipn'),
                'product_name' => 'Ledger Payment',
                'product_category' => 'membership-payment',
                'product_profile' => 'non-physical-goods',
                'cus_name' => $member['name'],
                'cus_email' => $member['email'],
                'cus_add1' => $member['address'],
                'cus_add2' => $member['address'],
                'cus_city' => $member['city'],
                'cus_state' => $member['city'],
                'cus_postcode' => $member['postcode'],
                'cus_country' => $member['country'],
                'cus_phone' => $member['phone'],
                'shipping_method' => 'NO',
                'num_of_item' => 1,
                'value_a' => $memberId,
                'value_b' => (string) $transaction->id,
                'value_c' => (string) ($transaction->note ?? ''),
            ]);

            $transaction->update([
                'status' => strtolower((string) ($response['status'] ?? 'pending')) === 'success' ? 'pending' : 'init_failed',
                'ssl_status' => $response['status'] ?? null,
                'session_key' => $response['sessionkey'] ?? null,
                'init_response' => json_encode($response),
                'last_status_at' => now(),
            ]);

            if (($response['status'] ?? null) !== 'SUCCESS' || empty($response['GatewayPageURL'])) {
                return response()->json([
                    'message' => $response['failedreason'] ?? 'Unable to initiate SSLCommerz payment.',
                ], 422);
            }

            return response()->json([
                'message' => 'Payment initiated.',
                'transaction_id' => $transaction->transaction_id,
                'gateway_url' => $response['GatewayPageURL'],
            ]);
        } catch (Throwable $e) {
            $transaction->update([
                'status' => 'init_failed',
                'ssl_status' => 'FAILED',
                'last_status_at' => now(),
            ]);

            report($e);

            return response()->json([
                'message' => 'Unable to connect to SSLCommerz.',
            ], 500);
        }
    }

    public function success(Request $request): RedirectResponse
    {
        $transaction = $this->findTransaction($request->input('tran_id'));

        if (! $transaction) {
            return redirect()->route('ledger', ['payment' => 'missing']);
        }

        $result = $this->finalizeSuccessfulPayment($transaction, $request->all());

        return redirect()->route('ledger', [
            'payment' => $result['ok'] ? 'success' : 'verification_failed',
            'tran_id' => $transaction->transaction_id,
        ]);
    }

    public function fail(Request $request): RedirectResponse
    {
        $this->markAsUnsuccessful($request->all(), 'failed');

        return redirect()->route('ledger', [
            'payment' => 'failed',
            'tran_id' => $request->input('tran_id'),
        ]);
    }

    public function cancel(Request $request): RedirectResponse
    {
        $this->markAsUnsuccessful($request->all(), 'cancelled');

        return redirect()->route('ledger', [
            'payment' => 'cancelled',
            'tran_id' => $request->input('tran_id'),
        ]);
    }

    public function ipn(Request $request): JsonResponse
    {
        $transaction = $this->findTransaction($request->input('tran_id'));

        if (! $transaction) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        if (($request->input('status') ?? '') === 'VALID' || ($request->input('status') ?? '') === 'VALIDATED') {
            $result = $this->finalizeSuccessfulPayment($transaction, $request->all());

            return response()->json(['message' => $result['ok'] ? 'Payment processed.' : 'Payment validation failed.']);
        }

        $this->markAsUnsuccessful($request->all(), strtolower((string) $request->input('status', 'failed')));

        return response()->json(['message' => 'Payment state recorded.']);
    }

    private function finalizeSuccessfulPayment(PaymentTransaction $transaction, array $payload): array
    {
        $transaction->update([
            'callback_payload' => json_encode($payload),
            'ssl_status' => $payload['status'] ?? $transaction->ssl_status,
            'validation_id' => $payload['val_id'] ?? $transaction->validation_id,
            'bank_transaction_id' => $payload['bank_tran_id'] ?? $transaction->bank_transaction_id,
            'card_type' => $payload['card_type'] ?? $transaction->card_type,
            'last_status_at' => now(),
        ]);

        $signaturePresent = isset($payload['verify_sign'], $payload['verify_key']);

        if ($signaturePresent && ! $this->sslCommerz->verifySignature($payload)) {
            $transaction->update([
                'status' => 'verification_failed',
                'validation_response' => json_encode(['reason' => 'Invalid SSLCommerz signature.']),
                'last_status_at' => now(),
            ]);

            return ['ok' => false];
        }

        try {
            $validation = $this->sslCommerz->validateOrder((string) ($payload['val_id'] ?? ''));
        } catch (Throwable $e) {
            report($e);

            $transaction->update([
                'status' => 'verification_failed',
                'validation_response' => json_encode(['reason' => 'Validation API request failed.']),
                'last_status_at' => now(),
            ]);

            return ['ok' => false];
        }

        $isValidStatus = in_array($validation['status'] ?? null, ['VALID', 'VALIDATED'], true);
        $amountMatches = (float) ($validation['amount'] ?? 0) === (float) $transaction->amount;
        $currencyMatches = strtoupper((string) ($validation['currency'] ?? '')) === strtoupper($transaction->currency);
        $transactionMatches = (string) ($validation['tran_id'] ?? '') === $transaction->transaction_id;

        if (! ($isValidStatus && $amountMatches && $currencyMatches && $transactionMatches)) {
            $transaction->update([
                'status' => 'verification_failed',
                'ssl_status' => $validation['status'] ?? $transaction->ssl_status,
                'validation_response' => json_encode($validation),
                'last_status_at' => now(),
            ]);

            return ['ok' => false];
        }

        DB::transaction(function () use ($transaction, $validation) {
            $paidAt = $this->parsePaidAt($validation['tran_date'] ?? null);

            $transaction->update([
                'status' => 'success',
                'ssl_status' => $validation['status'] ?? 'VALIDATED',
                'validation_id' => $validation['val_id'] ?? $transaction->validation_id,
                'bank_transaction_id' => $validation['bank_tran_id'] ?? $transaction->bank_transaction_id,
                'card_type' => $validation['card_type'] ?? $transaction->card_type,
                'store_amount' => $validation['store_amount'] ?? $transaction->store_amount,
                'validation_response' => json_encode($validation),
                'paid_at' => $paidAt,
                'last_status_at' => now(),
            ]);

            SuccessfulPaymentTransaction::updateOrCreate(
                ['payment_transaction_id' => $transaction->id],
                [
                    'transaction_id' => $transaction->transaction_id,
                    'member_id' => $transaction->member_id,
                    'member_name' => $transaction->member_name,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'validation_id' => $validation['val_id'] ?? null,
                    'bank_transaction_id' => $validation['bank_tran_id'] ?? null,
                    'card_type' => $validation['card_type'] ?? null,
                    'store_amount' => $validation['store_amount'] ?? null,
                    'note' => $transaction->note,
                    'validation_response' => json_encode($validation),
                    'paid_at' => $paidAt,
                ]
            );
        });

        return ['ok' => true];
    }

    private function markAsUnsuccessful(array $payload, string $status): void
    {
        $transaction = $this->findTransaction($payload['tran_id'] ?? null);

        if (! $transaction) {
            return;
        }

        $transaction->update([
            'status' => $status,
            'ssl_status' => strtoupper((string) ($payload['status'] ?? $status)),
            'callback_payload' => json_encode($payload),
            'validation_id' => $payload['val_id'] ?? $transaction->validation_id,
            'bank_transaction_id' => $payload['bank_tran_id'] ?? $transaction->bank_transaction_id,
            'card_type' => $payload['card_type'] ?? $transaction->card_type,
            'last_status_at' => now(),
        ]);
    }

    private function findTransaction(?string $transactionId): ?PaymentTransaction
    {
        $transactionId = trim((string) $transactionId);

        if ($transactionId === '') {
            return null;
        }

        return PaymentTransaction::query()->where('transaction_id', $transactionId)->first();
    }

    private function makeTransactionId(string $memberId): string
    {
        return Str::upper('CCL-'.$memberId.'-'.now()->format('YmdHis').'-'.Str::random(6));
    }

    private function parsePaidAt(?string $value): CarbonInterface
    {
        if (! $value) {
            return now();
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return now();
        }
    }

    private function getMemberContact(string $memberId): array
    {
        try {
            $member = DB::table('CustomerMst')
                ->where('PrvCusID', $memberId)
                ->select('CusName', 'Email', 'Mobile', 'Phone', 'Address', 'City')
                ->first();
        } catch (Throwable $e) {
            Log::warning('Unable to load member contact for payment initiation.', [
                'member_id' => $memberId,
                'error' => $e->getMessage(),
            ]);

            $member = null;
        }

        $name = trim((string) ($member->CusName ?? data_get(session('member'), 'name', 'Member')));
        $phone = preg_replace('/\D+/', '', (string) ($member->Mobile ?? $member->Phone ?? '')) ?: '01700000000';
        $email = filter_var((string) ($member->Email ?? ''), FILTER_VALIDATE_EMAIL)
            ? (string) $member->Email
            : 'member'.preg_replace('/\W+/', '', $memberId).'@example.com';

        return [
            'name' => $name !== '' ? $name : 'Member',
            'email' => $email,
            'phone' => $phone,
            'address' => trim((string) ($member->Address ?? 'Chittagong Club Ltd.')),
            'city' => trim((string) ($member->City ?? 'Chattogram')),
            'postcode' => '4000',
            'country' => 'Bangladesh',
        ];
    }
}
