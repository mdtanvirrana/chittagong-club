<?php

namespace App\Services\Auth;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RobiSmsService
{
    private const SUCCESS_STATUS_TEXT = 'success';

    public function __construct(
        private readonly HttpFactory $http,
    ) {
    }

    public function sendOtp(string $phone, string $message): void
    {
        $url = trim((string) config('services.robi_sms.url'));
        $apiKey = trim((string) config('services.robi_sms.api_key'));
        $type = trim((string) config('services.robi_sms.type', 'text'));
        $senderId = trim((string) config('services.robi_sms.sender_id'));
        $to = $this->normalizeRecipient($phone);
        $payload = [
            'api_key' => $apiKey,
            'type' => $type !== '' ? $type : 'text',
            'contacts' => $to,
            'senderid' => $senderId,
            'msg' => $message,
        ];

        if ($url === '' || $apiKey === '' || $senderId === '' || $to === '') {
            throw new RuntimeException('SMS gateway is not configured.');
        }

        Log::info('Sending OTP via MRAM SMS gateway.', $this->logContext($url, $payload));

        $response = $this->http
            ->timeout((int) config('services.robi_sms.timeout', 10))
            ->get($url, $payload);

        $this->ensureSuccessful($response, $url, $payload);
    }

    private function ensureSuccessful(Response $response, string $url, array $payload): void
    {
        $gatewayResponse = $this->parseGatewayResponse($response->body());

        if ($gatewayResponse !== null) {
            if ($this->gatewayResponseSuccessful($gatewayResponse)) {
                return;
            }

            Log::warning('MRAM SMS gateway rejected OTP request.', [
                ...$this->logContext($url, $payload),
                'gateway_response' => $gatewayResponse,
            ]);

            throw new RuntimeException($this->formatGatewayError($gatewayResponse));
        }

        if ($response->successful()) {
            return;
        }

        $message = trim((string) data_get($response->json(), 'message'));

        if ($message === '') {
            $message = trim($response->body());
        }

        Log::warning('MRAM SMS gateway request failed.', [
            ...$this->logContext($url, $payload),
            'http_status' => $response->status(),
            'response_excerpt' => $this->responseExcerpt($response->body()),
        ]);

        throw new RuntimeException($message !== '' ? $message : 'SMS request failed.');
    }

    private function logContext(string $url, array $payload): array
    {
        return [
            'sms_url' => $url,
            'sms_sender_id' => $payload['senderid'] ?? '',
            'sms_to_masked' => $this->maskPhone((string) ($payload['contacts'] ?? '')),
            'sms_to_length' => mb_strlen((string) ($payload['contacts'] ?? '')),
            'message_length' => mb_strlen((string) ($payload['msg'] ?? '')),
            'message_is_ascii' => ! preg_match('/[^\x00-\x7F]/', (string) ($payload['msg'] ?? '')),
        ];
    }

    private function responseExcerpt(string $body): string
    {
        return mb_substr(trim($body), 0, 500);
    }

    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        if (mb_strlen($digits) <= 6) {
            return str_repeat('*', mb_strlen($digits));
        }

        return mb_substr($digits, 0, 5) . str_repeat('*', max(0, mb_strlen($digits) - 8)) . mb_substr($digits, -3);
    }

    private function gatewayResponseSuccessful(array $gatewayResponse): bool
    {
        return strcasecmp($gatewayResponse['status_text'], self::SUCCESS_STATUS_TEXT) === 0
            || (
                $gatewayResponse['status'] === '0'
                && ($gatewayResponse['error_code'] === '' || $gatewayResponse['error_code'] === '0')
            );
    }

    private function formatGatewayError(array $gatewayResponse): string
    {
        $message = trim((string) ($gatewayResponse['error_text'] ?: $gatewayResponse['status_text']));
        $message = $message !== '' ? rtrim($message, '.') : 'SMS gateway rejected the request';

        $details = [];

        if ($gatewayResponse['error_code'] !== '' && $gatewayResponse['error_code'] !== '0') {
            $details[] = 'code ' . $gatewayResponse['error_code'];
        }

        if ($gatewayResponse['status'] !== '' && $gatewayResponse['status'] !== '0') {
            $details[] = 'status ' . $gatewayResponse['status'];
        }

        return $details === []
            ? $message . '.'
            : $message . ' (' . implode(', ', $details) . ').';
    }

    private function parseGatewayResponse(string $body): ?array
    {
        $body = trim($body);

        if ($body === '' || ! str_starts_with($body, '<')) {
            return null;
        }

        $previousValue = libxml_use_internal_errors(true);

        try {
            $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousValue);
        }

        if ($xml === false || ! isset($xml->ServiceClass)) {
            return null;
        }

        return [
            'status' => trim((string) $xml->ServiceClass->Status),
            'status_text' => trim((string) $xml->ServiceClass->StatusText),
            'error_code' => trim((string) $xml->ServiceClass->ErrorCode),
            'error_text' => trim((string) $xml->ServiceClass->ErrorText),
        ];
    }

    private function normalizeRecipient(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        if (mb_strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return '88' . $digits;
        }

        if (mb_strlen($digits) === 10 && str_starts_with($digits, '1')) {
            return '880' . $digits;
        }

        if (mb_strlen($digits) > 13) {
            return mb_substr($digits, -13);
        }

        return $digits;
    }
}
