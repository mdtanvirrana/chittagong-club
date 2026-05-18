<?php

namespace App\Http\Controllers;

use App\Models\NotifyMessage;
use App\Support\MemberSession;
use App\Support\NotifyOutbox;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationStreamController extends Controller
{
    public function stream(Request $request): StreamedResponse
    {
        $memberId = trim((string) data_get($request->session()->get(MemberSession::KEY, []), 'id'));

        abort_if($memberId === '', 401);

        $lastId = max(0, (int) ($request->header('Last-Event-ID') ?: $request->query('last_id', 0)));

        if ($lastId <= 0) {
            $lastId = (int) NotifyMessage::query()
                ->visible()
                ->where('status', 'published')
                ->forMember($memberId)
                ->max('id_notify_key');
        }

        if ($request->hasSession()) {
            $request->session()->save();
        }

        return response()->stream(function () use ($lastId, $memberId): void {
            $cursor = $lastId;
            $startedAt = time();
            $lastHeartbeatAt = 0;

            echo "retry: 3000\n\n";
            static::flush();

            while (! connection_aborted() && time() - $startedAt < 30) {
                $notifications = NotifyMessage::query()
                    ->visible()
                    ->where('status', 'published')
                    ->forMember($memberId)
                    ->where('id_notify_key', '>', $cursor)
                    ->orderBy('id_notify_key')
                    ->limit(20)
                    ->get();

                foreach ($notifications as $notification) {
                    $cursor = (int) $notification->id_notify_key;
                    $payload = json_encode(
                        NotifyOutbox::payload($notification),
                        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                    );

                    echo "id: {$cursor}\n";
                    echo "event: notification\n";
                    echo 'data: '.($payload ?: '{}')."\n\n";
                    static::flush();
                }

                if ($notifications->isEmpty() && time() - $lastHeartbeatAt >= 10) {
                    $lastHeartbeatAt = time();
                    echo ": heartbeat {$lastHeartbeatAt}\n\n";
                    static::flush();
                }

                sleep(2);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private static function flush(): void
    {
        if (ob_get_level() > 0) {
            @ob_flush();
        }

        @flush();
    }
}
