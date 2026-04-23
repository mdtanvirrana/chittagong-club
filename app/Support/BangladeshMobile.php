<?php

namespace App\Support;

class BangladeshMobile
{
    public static function normalize(?string $value): ?array
    {
        $raw = trim((string) $value);
        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00880')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '880')) {
            $national = '0' . substr($digits, 3);
        } elseif (str_starts_with($digits, '0')) {
            $national = $digits;
        } else {
            $national = '0' . $digits;
        }

        if (! preg_match('/^01[3-9]\d{8}$/', $national)) {
            return null;
        }

        $local = substr($national, 1);
        $e164Digits = '880' . $local;

        return [
            'raw' => $raw,
            'national' => $national,
            'local' => $local,
            'e164' => '+' . $e164Digits,
            'e164_digits' => $e164Digits,
            'formatted' => sprintf(
                '+880 %s %s %s',
                substr($local, 0, 2),
                substr($local, 2, 4),
                substr($local, 6, 4)
            ),
            'masked' => '+880 ' . substr($local, 0, 2) . str_repeat('*', 5) . substr($local, -3),
            'candidates' => array_values(array_unique([
                $e164Digits,
                $national,
                $local,
            ])),
        ];
    }
}
