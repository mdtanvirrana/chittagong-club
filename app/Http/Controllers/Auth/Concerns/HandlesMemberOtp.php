<?php

namespace App\Http\Controllers\Auth\Concerns;

use App\Services\Auth\RobiSmsService;
use Illuminate\Support\Facades\DB;
use Throwable;

trait HandlesMemberOtp
{
    private function dispatchOtp(array $phone, array $accounts, RobiSmsService $robiSms, string $note): array
    {
        $otp = (string) random_int(100000, 999999);
        $timestamp = now();
        $expiresAt = $timestamp->copy()->addMinutes(self::OTP_TTL_MINUTES);
        $message = $this->otpSmsMessage($otp);
        $otpId = DB::table('SMSSend_OTP')->insertGetId([
            'PrvcusID' => $this->truncateOtpText($this->otpMemberId($accounts), 50, '0'),
            'Mobile' => $this->truncateOtpText((string) data_get($phone, 'e164_digits'), 25, '0'),
            'OTP' => (int) $otp,
            'SDate' => $timestamp,
            'STime' => $timestamp->format('H:i:s'),
            'SMSText' => $this->truncateOtpText($message, 500, '0'),
            'Status' => 'PENDING',
            'EDate' => $timestamp,
            'ETime' => $timestamp->format('H:i:s'),
            'Note' => $this->truncateOtpText($note, 50, '0'),
        ]);

        try {
            $robiSms->sendOtp($this->smsRecipientPhone($phone), $message);
            $this->updateOtpStatus($otpId, 'SENT', $timestamp);
        } catch (Throwable $exception) {
            $this->updateOtpStatus($otpId, 'FAILED', $timestamp);

            throw $exception;
        }

        return [
            'otp_id' => $otpId,
            'sent_at' => $timestamp->timestamp,
            'expires_at' => $expiresAt->timestamp,
            'attempts' => 0,
            'verified_at' => null,
            'verified_until' => null,
        ];
    }

    private function otpRecord(int $otpId): ?object
    {
        if ($otpId <= 0) {
            return null;
        }

        return DB::table('SMSSend_OTP')
            ->where('id_otp', $otpId)
            ->first();
    }

    private function otpRecordPhone(?object $otpRecord): string
    {
        return trim((string) data_get($otpRecord, 'Mobile'));
    }

    private function updateOtpStatus(int $otpId, string $status, mixed $timestamp = null): void
    {
        if ($otpId <= 0) {
            return;
        }

        $timestamp ??= now();

        DB::table('SMSSend_OTP')
            ->where('id_otp', $otpId)
            ->update([
                'Status' => $this->truncateOtpText($status, 50, 'N'),
                'EDate' => $timestamp,
                'ETime' => $timestamp->format('H:i:s'),
            ]);
    }

    private function otpMemberId(array $accounts): string
    {
        $memberId = trim((string) data_get($accounts, '0.member_id'));

        return $memberId !== '' ? $memberId : '0';
    }

    private function smsRecipientPhone(array $phone): string
    {
        $national = trim((string) data_get($phone, 'national'));

        if ($national !== '') {
            return $national;
        }

        return trim((string) data_get($phone, 'e164_digits'));
    }

    private function otpSmsMessage(string $otp): string
    {
        $branding = $this->otpSmsBranding();
        $message = "{$branding['short_name']} Apps login OTP is {$otp}, Valid for " . self::OTP_TTL_MINUTES . ' minutes.';

        if ($branding['company_name'] !== '') {
            $message .= ' ' . rtrim($branding['company_name'], '.') . '.';
        }

        return $message;
    }

    private function otpSmsBranding(): array
    {
        $branding = [
            'short_name' => 'CCL',
            'company_name' => 'Chittagong Club Ltd.',
        ];

        try {
            $profile = DB::table('CPROFILE')->first();

            $branchName = trim((string) (
                data_get($profile, 'BranchName')
                ?? data_get($profile, 'branchName')
                ?? data_get($profile, 'branch_name')
                ?? data_get($profile, 'Branch')
                ?? ''
            ));
            $companyName = trim((string) (
                data_get($profile, 'CompanyName')
                ?? data_get($profile, 'companyName')
                ?? data_get($profile, 'COMPANY')
                ?? data_get($profile, 'Company')
                ?? ''
            ));

            $branding = [
                'short_name' => $this->otpSmsShortName($branchName, $companyName, $branding['short_name']),
                'company_name' => $companyName !== '' ? $companyName : $branding['company_name'],
            ];
        } catch (Throwable) {
            //
        }

        return $branding;
    }

    private function otpSmsShortName(string $branchName, string $companyName, string $fallback): string
    {
        $branchToken = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $branchName));

        if ($branchToken !== '' && strlen($branchToken) <= 8 && ! str_contains($branchToken, 'APP')) {
            return $branchToken;
        }

        $branchWithoutAppsToken = (string) preg_replace('/APPS?$/', '', $branchToken);

        if ($branchWithoutAppsToken !== '' && strlen($branchWithoutAppsToken) <= 8) {
            return $branchWithoutAppsToken;
        }

        $words = preg_split('/\s+/', preg_replace('/[^A-Za-z0-9 ]/', ' ', $companyName) ?: '') ?: [];
        $shortName = '';

        foreach ($words as $word) {
            $normalized = strtolower(trim($word));

            if ($normalized === '' || in_array($normalized, ['the', 'and', 'of'], true)) {
                continue;
            }

            $shortName .= strtoupper(substr($normalized, 0, 1));
        }

        return $shortName !== '' ? $shortName : $fallback;
    }

    private function truncateOtpText(?string $value, int $length, string $fallback): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $fallback;
        }

        return mb_substr($value, 0, $length);
    }
}
