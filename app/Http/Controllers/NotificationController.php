<?php

namespace App\Http\Controllers;

use App\Models\MemberApiUser;
use App\Models\NotifyMessage;
use App\Models\NotifyRead;
use App\Support\MemberSession;
use App\Support\NotifyOutbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        $memberId = $this->memberId($request);

        if ($memberId === '') {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! $this->wantsJson($request)) {
            return view('pages.notifications');
        }

        $limit = min(50, max(1, (int) $request->query('limit', 20)));
        $notifications = $this->notificationQuery($memberId)
            ->withReadState($memberId)
            ->orderByDesc('id_notify_key')
            ->limit($limit)
            ->get()
            ->map(fn (NotifyMessage $notification): array => NotifyOutbox::payload($notification) + [
                'read' => (bool) $notification->read_at,
                'read_at' => $notification->read_at ? (string) $notification->read_at : null,
            ])
            ->values();

        return response()->json([
            'unread_count' => $this->unreadCount($memberId),
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request, int $notification): JsonResponse
    {
        $memberId = $this->memberId($request);

        if ($memberId === '') {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $notificationRow = $this->visibleNotification($notification, $memberId);

        if (! $notificationRow) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        $this->markNotificationRead((int) $notificationRow->id_notify_key, $memberId);

        return response()->json([
            'message' => 'Notification marked as read.',
            'unread_count' => $this->unreadCount($memberId),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $memberId = $this->memberId($request);

        if ($memberId === '') {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        NotifyMessage::query()
            ->visible()
            ->where('status', 'published')
            ->forMember($memberId)
            ->whereNotExists(function ($query) use ($memberId): void {
                $query
                    ->selectRaw('1')
                    ->from('T_Notify_Read')
                    ->whereColumn('T_Notify_Read.id_notify_key', 'T_Notify.id_notify_key')
                    ->where('T_Notify_Read.member_id', $memberId);
            })
            ->pluck('id_notify_key')
            ->each(fn ($id): NotifyRead => $this->markNotificationRead((int) $id, $memberId));

        return response()->json([
            'message' => 'All notifications marked as read.',
            'unread_count' => 0,
        ]);
    }

    private function unreadCount(string $memberId): int
    {
        return (int) $this->notificationQuery($memberId)
            ->whereNotExists(function ($query) use ($memberId): void {
                $query
                    ->selectRaw('1')
                    ->from('T_Notify_Read')
                    ->whereColumn('T_Notify_Read.id_notify_key', 'T_Notify.id_notify_key')
                    ->where('T_Notify_Read.member_id', $memberId);
            })
            ->count();
    }

    private function notificationQuery(string $memberId)
    {
        return NotifyMessage::query()
            ->visible()
            ->where('status', 'published')
            ->forMember($memberId);
    }

    private function visibleNotification(int $id, string $memberId): ?NotifyMessage
    {
        return $this->notificationQuery($memberId)
            ->whereKey($id)
            ->first();
    }

    private function markNotificationRead(int $id, string $memberId): NotifyRead
    {
        $now = now();

        return NotifyRead::query()->updateOrCreate(
            [
                'id_notify_key' => $id,
                'member_id' => $memberId,
            ],
            [
                'read_at' => $now,
                'dtt_added' => $now,
                'dtt_mod' => $now,
            ]
        );
    }

    private function memberId(Request $request): string
    {
        $user = $request->user();

        if ($user instanceof MemberApiUser) {
            return trim((string) $user->member_id);
        }

        if (! $request->hasSession()) {
            return '';
        }

        return trim((string) data_get($request->session()->get(MemberSession::KEY, []), 'id'));
    }

    private function wantsJson(Request $request): bool
    {
        return $request->expectsJson()
            || $request->ajax()
            || str_contains((string) $request->header('Accept'), 'application/json');
    }
}
