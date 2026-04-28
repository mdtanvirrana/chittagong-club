<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Throwable;

class CompanyProfile
{
    public static function current(): array
    {
        static $profile;

        if ($profile !== null) {
            return $profile;
        }

        $profile = [
            'companyName' => 'Chittagong Club Ltd.',
            'branchName' => 'CCL',
            'shortName' => 'CCL',
            'companyAddress' => '',
            'companyAddressLines' => [],
            'companyAddressText' => '',
            'companyAddressMapQuery' => 'Chittagong Club Ltd.',
            'contactSummary' => '',
            'phones' => [],
            'website' => null,
            'websiteUrl' => null,
            'email' => null,
        ];

        try {
            $record = DB::table('CPROFILE')->first();

            $companyName = static::firstFilled($record, ['COMPANY', 'CompanyName', 'companyName', 'Company']);
            $branchName = static::firstFilled($record, ['BranchName', 'branchName', 'branch_name', 'Branch']);
            $address = static::firstFilled($record, ['HOAddress', 'hoAddress', 'Address']);
            $contactSummary = static::singleLine(
                static::firstFilled($record, ['HOTel', 'HOtel', 'HOTEL', 'HOTelNo', 'Tel'])
            );
            $website = static::extractWebsite($contactSummary)
                ?: static::firstFilled($record, ['Website', 'Web', 'website', 'web']);
            $email = static::extractEmail($contactSummary)
                ?: static::firstFilled($record, ['Email', 'email', 'Mail', 'mail']);
            $phones = static::extractPhones($contactSummary);

            $companyName = $companyName !== '' ? $companyName : $profile['companyName'];
            $branchName = $branchName !== '' ? $branchName : $profile['branchName'];
            $shortName = static::resolveShortName($branchName, $companyName, $profile['shortName']);
            $addressLines = array_values(array_filter(
                preg_split('/\r\n|\r|\n/', $address) ?: [],
                fn (?string $line): bool => trim((string) $line) !== ''
            ));
            $addressText = implode(', ', $addressLines);

            $profile = [
                'companyName' => $companyName,
                'branchName' => $branchName,
                'shortName' => $shortName,
                'companyAddress' => $address,
                'companyAddressLines' => $addressLines,
                'companyAddressText' => $addressText,
                'companyAddressMapQuery' => trim($companyName . ' ' . $address),
                'contactSummary' => $contactSummary,
                'phones' => $phones,
                'website' => $website !== '' ? $website : null,
                'websiteUrl' => static::websiteUrl($website),
                'email' => $email !== '' ? $email : null,
            ];
        } catch (Throwable) {
            //
        }

        return $profile;
    }

    private static function firstFilled(mixed $record, array $keys): string
    {
        foreach ($keys as $key) {
            $value = trim((string) data_get($record, $key));

            if ($value !== '' && $value !== '0') {
                return $value;
            }
        }

        return '';
    }

    private static function singleLine(string $value): string
    {
        return trim((string) preg_replace('/\s+/', ' ', $value));
    }

    private static function resolveShortName(string $branchName, string $companyName, string $fallback): string
    {
        $branchToken = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $branchName));

        if ($branchToken !== '' && strlen($branchToken) <= 8 && ! str_contains($branchToken, 'APP')) {
            return $branchToken;
        }

        $words = preg_split('/\s+/', preg_replace('/[^A-Za-z0-9 ]/', ' ', $companyName) ?: '') ?: [];
        $shortName = '';

        foreach ($words as $word) {
            $normalized = strtolower(trim($word));

            if ($normalized === '' || in_array($normalized, ['the', 'and', 'of', 'ltd', 'limited'], true)) {
                continue;
            }

            $shortName .= strtoupper(substr($normalized, 0, 1));
        }

        return $shortName !== '' ? $shortName : $fallback;
    }

    private static function extractWebsite(string $contactSummary): string
    {
        if ($contactSummary === '') {
            return '';
        }

        if (preg_match('/\bweb(?:site)?\s*:\s*([^\s,]+)/i', $contactSummary, $matches) !== 1) {
            return '';
        }

        return rtrim(trim((string) ($matches[1] ?? '')), ".,; ");
    }

    private static function extractEmail(string $contactSummary): string
    {
        if ($contactSummary === '') {
            return '';
        }

        if (preg_match('/\bemail\s*:\s*([^\s,]+)/i', $contactSummary, $matches) !== 1) {
            return '';
        }

        return rtrim(trim((string) ($matches[1] ?? '')), ".,; ");
    }

    private static function extractPhones(string $contactSummary): array
    {
        if ($contactSummary === '') {
            return [];
        }

        $phoneSegment = preg_split('/\bweb(?:site)?\s*:/i', $contactSummary, 2)[0] ?? $contactSummary;
        $phoneSegment = preg_split('/\bemail\s*:/i', $phoneSegment, 2)[0] ?? $phoneSegment;
        $phoneSegment = preg_replace('/^phone numbers?\s*:\s*/i', '', $phoneSegment) ?? $phoneSegment;

        return array_values(array_filter(
            array_map(
                fn (?string $entry): string => trim((string) $entry),
                preg_split('/\s*,\s*/', $phoneSegment) ?: []
            ),
            fn (string $entry): bool => $entry !== '' && preg_match('/\d/', $entry) === 1
        ));
    }

    private static function websiteUrl(?string $website): ?string
    {
        $website = trim((string) $website);

        if ($website === '') {
            return null;
        }

        return preg_match('/^https?:\/\//i', $website) === 1
            ? $website
            : 'https://' . ltrim($website, '/');
    }
}
