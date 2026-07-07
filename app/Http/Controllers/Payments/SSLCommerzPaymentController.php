<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\InitiateSslCommerzPaymentRequest;
use App\Models\MemberApiUser;
use App\Models\PaymentTransaction;
use App\Models\SuccessfulPaymentTransaction;
use App\Services\Payments\SSLCommerzService;
use App\Support\MemberAccess;
use App\Support\MemberSession;
use App\Support\NotifyOutbox;
use App\Support\PortalCache;
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
        $memberContext = $this->currentMemberContext($request);
        $memberId = (string) data_get($memberContext, 'id');

        if ($memberId === '') {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        try {
            $this->sslCommerz->ensureConfigured();
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $member = $this->getMemberContact($memberId, (string) data_get($memberContext, 'name', 'Member'));
        $channel = (string) data_get($memberContext, 'channel', 'web');
        $transactionId = $this->makeTransactionId($memberId, $channel);

        $transaction = PaymentTransaction::create([
            'transaction_id' => $transactionId,
            'member_id' => $memberId,
            'member_name' => data_get($memberContext, 'name'),
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
                'success_url' => $this->callbackUrl('success'),
                'fail_url' => $this->callbackUrl('fail'),
                'cancel_url' => $this->callbackUrl('cancel'),
                'ipn_url' => $this->callbackUrl('ipn'),
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
            PortalCache::clearMemberRelatedCaches($memberId);

            if (($response['status'] ?? null) !== 'SUCCESS' || empty($response['GatewayPageURL'])) {
                return response()->json([
                    'message' => $response['failedreason'] ?? 'Unable to initiate SSLCommerz payment.',
                ], 422);
            }

            return response()->json([
                'message' => 'Payment initiated.',
                'transaction_id' => $transaction->transaction_id,
                'gateway_url' => $response['GatewayPageURL'],
                'return_url' => $channel === 'mobile' ? $this->mobileReturnUrl() : null,
            ]);
        } catch (Throwable $e) {
            $transaction->update([
                'status' => 'init_failed',
                'ssl_status' => 'FAILED',
                'last_status_at' => now(),
            ]);
            PortalCache::clearMemberRelatedCaches($memberId);

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

        return $this->paymentRedirect($transaction, $result['state']);
    }

    public function fail(Request $request): RedirectResponse
    {
        $transaction = $this->markAsUnsuccessful($request->all(), 'failed');

        return $transaction
            ? $this->paymentRedirect($transaction, 'failed')
            : redirect()->route('ledger', ['payment' => 'failed', 'tran_id' => $request->input('tran_id')]);
    }

    public function cancel(Request $request): RedirectResponse
    {
        $transaction = $this->markAsUnsuccessful($request->all(), 'cancelled');

        return $transaction
            ? $this->paymentRedirect($transaction, 'cancelled')
            : redirect()->route('ledger', ['payment' => 'cancelled', 'tran_id' => $request->input('tran_id')]);
    }

    public function ipn(Request $request): JsonResponse
    {
        $transaction = $this->findTransaction($request->input('tran_id'));

        if (! $transaction) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        if (($request->input('status') ?? '') === 'VALID' || ($request->input('status') ?? '') === 'VALIDATED') {
            $result = $this->finalizeSuccessfulPayment($transaction, $request->all());

            return response()->json([
                'message' => match ($result['state']) {
                    'success' => 'Payment processed.',
                    'held' => 'Payment held for manual review.',
                    default => 'Payment validation failed.',
                },
            ]);
        }

        $this->markAsUnsuccessful($request->all(), strtolower((string) $request->input('status', 'failed')));

        return response()->json(['message' => 'Payment state recorded.']);
    }

    public function show(Request $request, string $transactionId): JsonResponse
    {
        $memberId = (string) data_get($this->currentMemberContext($request), 'id');
        $transaction = $this->findTransaction($transactionId);

        if (! $transaction || $transaction->member_id !== $memberId) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        return response()->json([
            'transaction' => $this->transactionPayload($transaction),
        ]);
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

        $validationId = trim((string) ($payload['val_id'] ?? $transaction->validation_id ?? ''));

        if ($validationId === '') {
            $transaction->update([
                'status' => 'verification_failed',
                'validation_response' => json_encode(['reason' => 'Validation ID is missing from the SSLCommerz callback.']),
                'last_status_at' => now(),
            ]);
            PortalCache::clearMemberRelatedCaches($transaction->member_id);

            return ['ok' => false, 'state' => 'verification_failed'];
        }

        $signaturePresent = isset($payload['verify_sign'], $payload['verify_key']);

        if ($signaturePresent && ! $this->sslCommerz->verifySignature($payload)) {
            $transaction->update([
                'status' => 'verification_failed',
                'validation_response' => json_encode(['reason' => 'Invalid SSLCommerz signature.']),
                'last_status_at' => now(),
            ]);
            PortalCache::clearMemberRelatedCaches($transaction->member_id);

            return ['ok' => false, 'state' => 'verification_failed'];
        }

        try {
            $validation = $this->sslCommerz->validateOrder($validationId);
        } catch (Throwable $e) {
            report($e);

            $transaction->update([
                'status' => 'verification_failed',
                'validation_response' => json_encode(['reason' => 'Validation API request failed.']),
                'last_status_at' => now(),
            ]);
            PortalCache::clearMemberRelatedCaches($transaction->member_id);

            return ['ok' => false, 'state' => 'verification_failed'];
        }

        $isValidStatus = in_array($validation['status'] ?? null, ['VALID', 'VALIDATED'], true);
        $amountMatches = $this->amountsMatch($validation['amount'] ?? 0, $transaction->amount);
        $currencyMatches = strtoupper((string) ($validation['currency'] ?? '')) === strtoupper($transaction->currency);
        $transactionMatches = (string) ($validation['tran_id'] ?? '') === $transaction->transaction_id;

        if (! ($isValidStatus && $amountMatches && $currencyMatches && $transactionMatches)) {
            $transaction->update([
                'status' => 'verification_failed',
                'ssl_status' => $validation['status'] ?? $transaction->ssl_status,
                'validation_response' => json_encode($validation),
                'last_status_at' => now(),
            ]);
            PortalCache::clearMemberRelatedCaches($transaction->member_id);

            return ['ok' => false, 'state' => 'verification_failed'];
        }

        $paidAt = $this->parsePaidAt($validation['tran_date'] ?? null);

        if ($this->shouldHoldForReview($validation)) {
            $transaction->update([
                'status' => 'held',
                'ssl_status' => $validation['status'] ?? 'VALIDATED',
                'validation_id' => $validation['val_id'] ?? $transaction->validation_id,
                'bank_transaction_id' => $validation['bank_tran_id'] ?? $transaction->bank_transaction_id,
                'card_type' => $validation['card_type'] ?? $transaction->card_type,
                'store_amount' => $validation['store_amount'] ?? $transaction->store_amount,
                'validation_response' => json_encode($validation),
                'paid_at' => $paidAt,
                'last_status_at' => now(),
            ]);

            Log::warning('SSLCommerz transaction held for manual review due to high risk level.', [
                'transaction_id' => $transaction->transaction_id,
                'member_id' => $transaction->member_id,
                'risk_level' => $validation['risk_level'] ?? null,
                'risk_title' => $validation['risk_title'] ?? null,
            ]);
            PortalCache::clearMemberRelatedCaches($transaction->member_id);

            return ['ok' => false, 'state' => 'held'];
        }

        DB::transaction(function () use ($transaction, $validation, $paidAt) {
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
        PortalCache::clearMemberRelatedCaches($transaction->member_id);

        if ($freshTransaction = $transaction->fresh()) {
            NotifyOutbox::paymentSucceeded($freshTransaction);
        }

        return ['ok' => true, 'state' => 'success'];
    }

    private function markAsUnsuccessful(array $payload, string $status): ?PaymentTransaction
    {
        $transaction = $this->findTransaction($payload['tran_id'] ?? null);

        if (! $transaction) {
            return null;
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
        PortalCache::clearMemberRelatedCaches($transaction->member_id);

        return $transaction->fresh();
    }

    private function findTransaction(?string $transactionId): ?PaymentTransaction
    {
        $transactionId = trim((string) $transactionId);

        if ($transactionId === '') {
            return null;
        }

        return PaymentTransaction::query()->where('transaction_id', $transactionId)->first();
    }

    private function makeTransactionId(string $memberId, string $channel = 'web'): string
    {
        $prefix = $channel === 'mobile' ? 'CCL-M-' : 'CCL-';

        return Str::upper($prefix.$memberId.'-'.now()->format('YmdHis').'-'.Str::random(6));
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

    private function callbackUrl(string $type): string
    {
        $configured = trim((string) config('services.sslcommerz.'.$type.'_url'));

        if ($configured !== '') {
            return $configured;
        }

        return route('payments.sslcommerz.'.$type);
    }

    private function amountsMatch(float|string|int $expected, float|string|int $actual): bool
    {
        return number_format((float) $expected, 2, '.', '') === number_format((float) $actual, 2, '.', '');
    }

    private function shouldHoldForReview(array $validation): bool
    {
        return (string) ($validation['risk_level'] ?? '') === '1';
    }

    private function getMemberContact(string $memberId, ?string $fallbackName = null): array
    {
        try {
            $member = MemberAccess::activeMemberQuery('c', 'cc')
                ->where('c.PrvCusID', $memberId)
                ->select('c.CusName', 'c.Email', 'c.Mobile', 'c.Phone', 'c.Address', 'c.City')
                ->first();
        } catch (Throwable $e) {
            Log::warning('Unable to load member contact for payment initiation.', [
                'member_id' => $memberId,
                'error' => $e->getMessage(),
            ]);

            $member = null;
        }

        $name = trim((string) ($member->CusName ?? $fallbackName ?? 'Member'));
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

    private function currentMemberContext(Request $request): array
    {
        $user = $request->user();

        if ($user instanceof MemberApiUser) {
            return [
                'id' => $user->member_id,
                'name' => $user->display_name,
                'channel' => 'mobile',
            ];
        }

        $member = $request->session()->get(MemberSession::KEY, []);

        return [
            'id' => trim((string) data_get($member, 'id')),
            'name' => trim((string) data_get($member, 'name', 'Member')),
            'channel' => 'web',
        ];
    }

    private function mobileReturnUrl(): string
    {
        return trim((string) config('services.mobile_app.payment_return_url', 'cclapps://payment-result'));
    }

    private function paymentRedirect(PaymentTransaction $transaction, string $state): RedirectResponse
    {
        if ($this->isMobileTransaction($transaction)) {
            return redirect()->away($this->appendReturnQuery($this->mobileReturnUrl(), [
                'payment' => $state,
                'tran_id' => $transaction->transaction_id,
            ]));
        }

        return redirect()->route('ledger', [
            'payment' => $state,
            'tran_id' => $transaction->transaction_id,
        ]);
    }

    private function appendReturnQuery(string $url, array $query): string
    {
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . http_build_query($query);
    }

    private function isMobileTransaction(PaymentTransaction $transaction): bool
    {
        return Str::startsWith((string) $transaction->transaction_id, 'CCL-M-');
    }

    private function transactionPayload(PaymentTransaction $transaction): array
    {
        return [
            'transaction_id' => $transaction->transaction_id,
            'amount' => (float) $transaction->amount,
            'currency' => $transaction->currency,
            'status' => $transaction->status,
            'ssl_status' => $transaction->ssl_status,
            'card_type' => $transaction->card_type,
            'bank_transaction_id' => $transaction->bank_transaction_id,
            'paid_at' => optional($transaction->paid_at)->toIso8601String(),
            'updated_at' => optional($transaction->updated_at)->toIso8601String(),
        ];
    }
}
