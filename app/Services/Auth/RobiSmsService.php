<?php

namespace App\Services\Auth;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use RuntimeException;

class RobiSmsService
{
    public function __construct(
        private readonly HttpFactory $http,
    ) {
    }

    public function sendOtp(string $phone, string $message): void
    {
        $url = trim((string) config('services.robi_sms.url'));
        $token = trim((string) config('services.robi_sms.token'));
        $senderId = trim((string) config('services.robi_sms.sender_id'));

        if ($url === '' || $token === '') {
            throw new RuntimeException('Robi SMS is not configured.');
        }

        $payload = [
            config('services.robi_sms.to_field', 'to') => $phone,
            config('services.robi_sms.message_field', 'message') => $message,
        ];

        if ($senderId !== '') {
            $payload[config('services.robi_sms.sender_field', 'sender_id')] = $senderId;
        }

        $response = $this->http
            ->acceptJson()
            ->timeout((int) config('services.robi_sms.timeout', 10))
            ->withHeaders([
                config('services.robi_sms.token_header', 'Authorization') => $token,
            ])
            ->post($url, $payload);

        $this->ensureSuccessful($response);
    }

    private function ensureSuccessful(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        $message = trim((string) data_get($response->json(), 'message'));

        throw new RuntimeException($message !== '' ? $message : 'Robi SMS request failed.');
    }
}
