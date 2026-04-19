<?php

namespace App\Services\Payments;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SSLCommerzService
{
    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function initiate(array $payload): array
    {
        $response = Http::asForm()
            ->timeout(30)
            ->post($this->initUrl(), array_merge($payload, [
                'store_id' => $this->storeId(),
                'store_passwd' => $this->storePassword(),
            ]));

        if ($response->failed()) {
            throw new RequestException($response);
        }

        return $response->json() ?? [];
    }

    public function validateOrder(string $validationId): array
    {
        $response = Http::timeout(30)->get($this->validationUrl(), [
            'val_id' => $validationId,
            'store_id' => $this->storeId(),
            'store_passwd' => $this->storePassword(),
            'v' => 1,
            'format' => 'json',
        ]);

        if ($response->failed()) {
            throw new RequestException($response);
        }

        return $response->json() ?? [];
    }

    public function verifySignature(array $payload): bool
    {
        $verifySign = (string) ($payload['verify_sign'] ?? '');
        $verifyKey = (string) ($payload['verify_key'] ?? '');

        if ($verifySign === '' || $verifyKey === '') {
            return false;
        }

        $keys = array_filter(array_map('trim', explode(',', $verifyKey)));
        $signedValues = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $payload)) {
                $signedValues[$key] = (string) $payload[$key];
            }
        }

        $signedValues['store_passwd'] = md5($this->storePassword());
        ksort($signedValues);

        $hashSource = urldecode(http_build_query($signedValues));

        return md5($hashSource) === $verifySign;
    }

    public function ensureConfigured(): void
    {
        if ($this->storeId() === '' || $this->storePassword() === '') {
            throw new RuntimeException('SSLCommerz credentials are missing.');
        }
    }

    public function currency(): string
    {
        return (string) config('services.sslcommerz.currency', 'BDT');
    }

    private function initUrl(): string
    {
        return $this->baseUrl().'/gwprocess/v4/api.php';
    }

    private function validationUrl(): string
    {
        return $this->baseUrl().'/validator/api/validationserverAPI.php';
    }

    private function baseUrl(): string
    {
        return rtrim((string) (config('services.sslcommerz.sandbox', true)
            ? config('services.sslcommerz.sandbox_url', 'https://sandbox.sslcommerz.com')
            : config('services.sslcommerz.live_url', 'https://securepay.sslcommerz.com')), '/');
    }

    private function storeId(): string
    {
        return trim((string) config('services.sslcommerz.store_id'));
    }

    private function storePassword(): string
    {
        return trim((string) config('services.sslcommerz.store_password'));
    }
}
