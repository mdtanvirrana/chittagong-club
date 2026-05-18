<?php

namespace App\Support;

use App\Models\CircularItem;
use App\Models\NoticeMessage;
use App\Models\NotifyMessage;
use App\Models\PaymentTransaction;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class NotifyOutbox
{
    private const DUE_THRESHOLDS = [40000, 45000, 50000];

    public static function circularPosted(CircularItem $circular, int $userId = 0): void
    {
        if (! static::isPublishable($circular)) {
            static::disableNotification('circular', (string) $circular->getKey(), $userId);

            return;
        }

        $title = trim((string) ($circular->tx_title ?: 'Club circular'));

        static::upsertNotification(
            sourceType: 'circular',
            sourceTable: $circular->getTable(),
            sourceKey: (string) $circular->getKey(),
            sourceVersion: (int) ($circular->id_career_ver ?? 1),
            title: 'New Club Circular',
            body: static::announcementBody('circular', $title, $circular->excerpt),
            actionUrl: route('circulars'),
            scheduledAt: $circular->dtt_ad_start,
            userId: $userId,
            payload: [
                'screen' => 'circulars',
                'source_id' => (int) $circular->getKey(),
                'image_url' => $circular->display_image_url,
            ],
        );
    }

    public static function noticePosted(NoticeMessage $notice, int $userId = 0): void
    {
        if (! static::isPublishable($notice)) {
            static::disableNotification('notice', (string) $notice->getKey(), $userId);

            return;
        }

        $title = trim((string) ($notice->tx_title ?: 'Club notice'));

        static::upsertNotification(
            sourceType: 'notice',
            sourceTable: $notice->getTable(),
            sourceKey: (string) $notice->getKey(),
            sourceVersion: (int) ($notice->id_message_ver ?? 1),
            title: 'New Club Notice',
            body: static::announcementBody('notice', $title, $notice->excerpt),
            actionUrl: route('notice-board'),
            scheduledAt: static::noticeScheduledAt($notice),
            userId: $userId,
            payload: [
                'screen' => 'notice-board',
                'source_id' => (int) $notice->getKey(),
                'image_url' => $notice->image_url,
            ],
        );
    }

    public static function paymentSucceeded(PaymentTransaction $transaction): void
    {
        $memberId = trim((string) $transaction->member_id);

        if ($memberId === '') {
            return;
        }

        static::upsertNotification(
            sourceType: 'payment',
            sourceTable: $transaction->getTable(),
            sourceKey: (string) $transaction->transaction_id,
            sourceVersion: (int) $transaction->getKey(),
            event: 'success',
            targetType: 'member',
            targetMemberId: $memberId,
            title: 'Payment Received Successfully',
            body: 'Dear Member, your payment of BDT '.static::formatMoney((float) $transaction->amount).' has been received successfully. Thank you for keeping your account up to date.',
            actionUrl: route('ledger'),
            scheduledAt: $transaction->paid_at ?? now(),
            userId: 0,
            payload: [
                'screen' => 'ledger',
                'source_id' => (int) $transaction->getKey(),
                'transaction_id' => (string) $transaction->transaction_id,
                'amount' => (float) $transaction->amount,
                'currency' => (string) $transaction->currency,
            ],
        );
    }

    public static function dueReminder(string $memberId, float $totalDue, float $creditLimit = 0): void
    {
        $memberId = trim($memberId);

        if ($memberId === '' || $totalDue < min(self::DUE_THRESHOLDS)) {
            return;
        }

        $threshold = collect(self::DUE_THRESHOLDS)
            ->filter(fn (int $value): bool => $totalDue >= $value)
            ->max();

        if (! $threshold) {
            return;
        }

        $monthKey = now()->format('Y-m');

        static::upsertNotification(
            sourceType: 'ledger',
            sourceTable: 'Customer_ledger',
            sourceKey: $memberId.':due:'.$threshold.':'.$monthKey,
            sourceVersion: (int) $threshold,
            event: 'due_reminder',
            targetType: 'member',
            targetMemberId: $memberId,
            title: 'Ledger Due Reminder',
            body: 'Dear Member, your outstanding balance has reached BDT '.static::formatMoney($totalDue).'. Please review your ledger and settle the amount at your convenience.',
            actionUrl: route('ledger'),
            scheduledAt: now(),
            userId: 0,
            payload: [
                'screen' => 'ledger',
                'threshold' => $threshold,
                'total_due' => $totalDue,
                'credit_limit' => $creditLimit,
                'month' => $monthKey,
            ],
        );
    }

    public static function payload(NotifyMessage $notification): array
    {
        return [
            'id' => (int) $notification->id_notify_key,
            'type' => (string) $notification->source_type,
            'event' => (string) $notification->event,
            'title' => (string) $notification->title,
            'body' => (string) ($notification->body ?? ''),
            'action_url' => $notification->action_url,
            'payload' => $notification->payloadArray(),
            'scheduled_at' => optional($notification->scheduled_at)->toIso8601String(),
            'created_at' => optional($notification->dtt_added)->toIso8601String(),
            'target_type' => (string) $notification->target_type,
        ];
    }

    private static function upsertNotification(
        string $sourceType,
        string $sourceTable,
        string $sourceKey,
        int $sourceVersion,
        string $title,
        ?string $body,
        string $actionUrl,
        CarbonInterface|string|null $scheduledAt,
        int $userId,
        array $payload = [],
        string $event = 'posted',
        string $targetType = 'all_members',
        string $targetMemberId = '*',
    ): void {
        $now = now();
        $scheduledAt = $scheduledAt instanceof CarbonInterface ? $scheduledAt : ($scheduledAt ? Carbon::parse($scheduledAt) : $now);

        $notification = NotifyMessage::query()->updateOrCreate(
            [
                'source_type' => $sourceType,
                'source_key' => $sourceKey,
                'event' => $event,
                'target_type' => $targetType,
                'target_member_id' => $targetMemberId,
            ],
            [
                'id_notify_ver' => max(1, $sourceVersion),
                'is_active' => true,
                'is_online' => true,
                'source_table' => $sourceTable,
                'source_version' => $sourceVersion,
                'title' => Str::limit($title !== '' ? $title : 'New notification', 255, ''),
                'body' => trim((string) $body) ?: null,
                'action_url' => $actionUrl,
                'payload' => json_encode($payload + [
                    'source_table' => $sourceTable,
                    'source_key' => $sourceKey,
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'status' => 'published',
                'scheduled_at' => $scheduledAt,
                'dtt_added' => $now,
                'dtt_mod' => $now,
                'id_user_mod' => $userId,
            ]
        );

        static::pushIfReady($notification);
    }

    private static function pushIfReady(NotifyMessage $notification): void
    {
        if (! $notification->wasRecentlyCreated
            && ! $notification->wasChanged(['source_version', 'status', 'is_active', 'is_online'])) {
            return;
        }

        if (! $notification->is_active
            || ! $notification->is_online
            || (string) $notification->status !== 'published'
            || ($notification->scheduled_at && $notification->scheduled_at->isFuture())) {
            return;
        }

        app()->terminating(function () use ($notification): void {
            $freshNotification = $notification->fresh();

            app(ExpoPushNotificationService::class)->send($freshNotification ?: $notification);
        });
    }

    private static function isPublishable($model): bool
    {
        return (bool) ($model->is_active ?? false) && (bool) ($model->is_online ?? false);
    }

    private static function disableNotification(string $sourceType, string $sourceKey, int $userId): void
    {
        NotifyMessage::query()
            ->where('source_type', $sourceType)
            ->where('source_key', $sourceKey)
            ->where('event', 'posted')
            ->where('target_type', 'all_members')
            ->where('target_member_id', '*')
            ->update([
                'is_active' => false,
                'is_online' => false,
                'status' => 'disabled',
                'dtt_mod' => now(),
                'id_user_mod' => $userId,
            ]);
    }

    private static function noticeScheduledAt(NoticeMessage $notice): CarbonInterface
    {
        $date = $notice->Edate ? $notice->Edate->copy() : now();
        $time = trim((string) $notice->Etime);

        if (preg_match('/^\d{2}:\d{2}/', $time) === 1) {
            [$hour, $minute] = array_map('intval', explode(':', substr($time, 0, 5)));

            return $date->setTime($hour, $minute);
        }

        return $date;
    }

    private static function announcementBody(string $type, string $title, ?string $excerpt): string
    {
        $label = $type === 'notice' ? 'notice' : 'circular';
        $message = 'Dear Member, a new club '.$label.' has been published: '.$title.'.';
        $excerpt = trim((string) $excerpt);

        if ($excerpt !== '') {
            $message .= ' '.Str::limit($excerpt, 120);
        }

        return $message.' Please open Notifications to review the details.';
    }

    private static function formatMoney(float $amount): string
    {
        return number_format($amount, 2);
    }
}
