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
        $message = "{$branding['branch_name']} login OTP is {$otp}, Valid for " . self::OTP_TTL_MINUTES . ' minutes.';

        if ($branding['company_name'] !== '') {
            $message .= ' ' . $branding['company_name'];
        }

        return $message;
    }

    private function otpSmsBranding(): array
    {
        $branding = [
            'branch_name' => 'CCLApps',
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
                'branch_name' => $branchName !== '' ? $branchName : $branding['branch_name'],
                'company_name' => $companyName !== '' ? $companyName : $branding['company_name'],
            ];
        } catch (Throwable) {
            //
        }

        return $branding;
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
