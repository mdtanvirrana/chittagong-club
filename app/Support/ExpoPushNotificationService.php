<?php

namespace App\Support;

use App\Models\NotifyDevice;
use App\Models\NotifyMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ExpoPushNotificationService
{
    private const ENDPOINT = 'https://exp.host/--/api/v2/push/send';

    public function send(NotifyMessage $notification): void
    {
        if (! Schema::hasTable('T_Notify_Device')) {
            return;
        }

        $query = NotifyDevice::query()->where('is_active', true);

        if ((string) $notification->target_type === 'member') {
            $query->where('member_id', (string) $notification->target_member_id);
        }

        $payload = NotifyOutbox::payload($notification);

        $query
            ->orderBy('id_notify_device_key')
            ->chunk(100, function ($devices) use ($payload, $notification): void {
                $messages = $devices
                    ->map(fn (NotifyDevice $device): array => [
                        'to' => $device->expo_push_token,
                        'sound' => 'default',
                        'title' => (string) $payload['title'],
                        'body' => (string) ($payload['body'] ?? ''),
                        'priority' => 'high',
                        'channelId' => 'default',
                        'data' => [
                            'notification_id' => $payload['id'],
                            'type' => $payload['type'],
                            'event' => $payload['event'],
                            'action_url' => $payload['action_url'],
                            'payload' => $payload['payload'],
                        ],
                    ])
                    ->values()
                    ->all();

                if ($messages === []) {
                    return;
                }

                try {
                    $request = Http::acceptJson()->timeout(8);
                    $accessToken = trim((string) config('services.expo.access_token'));

                    if ($accessToken !== '') {
                        $request = $request->withToken($accessToken);
                    }

                    $response = $request->post(self::ENDPOINT, $messages);

                    if (! $response->successful()) {
                        $this->recordFailure($notification, 'Expo push failed with HTTP '.$response->status().'.');

                        Log::warning('Expo push notification request failed.', [
                            'notification_id' => $notification->id_notify_key,
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ]);

                        return;
                    }

                    $notification->forceFill([
                        'pushed_at' => now(),
                        'last_error' => null,
                        'push_attempts' => ((int) $notification->push_attempts) + 1,
                        'dtt_mod' => now(),
                    ])->save();
                } catch (Throwable $e) {
                    $this->recordFailure($notification, $e->getMessage());

                    Log::warning('Unable to send Expo push notification.', [
                        'notification_id' => $notification->id_notify_key,
                        'error' => $e->getMessage(),
                    ]);
                }
            });
    }

    private function recordFailure(NotifyMessage $notification, string $error): void
    {
        $notification->forceFill([
            'last_error' => $error,
            'push_attempts' => ((int) $notification->push_attempts) + 1,
            'dtt_mod' => now(),
        ])->save();
    }
}
