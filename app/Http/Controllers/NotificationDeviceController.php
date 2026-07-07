<?php

namespace App\Http\Controllers;

use App\Models\MemberApiUser;
use App\Models\NotifyDevice;
use App\Support\MemberSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationDeviceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $memberId = $this->memberId($request);

        if ($memberId === '') {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'expo_push_token' => ['required', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:24'],
            'device_id' => ['nullable', 'string', 'max:120'],
            'device_name' => ['nullable', 'string', 'max:160'],
            'app_version' => ['nullable', 'string', 'max:40'],
        ]);

        $now = now();

        NotifyDevice::query()->updateOrCreate(
            ['expo_push_token' => $validated['expo_push_token']],
            [
                'member_id' => $memberId,
                'platform' => $validated['platform'] ?? null,
                'device_id' => $validated['device_id'] ?? null,
                'device_name' => $validated['device_name'] ?? null,
                'app_version' => $validated['app_version'] ?? null,
                'is_active' => true,
                'last_seen_at' => $now,
                'dtt_added' => $now,
                'dtt_mod' => $now,
            ]
        );

        return response()->json([
            'message' => 'Notification device registered.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $memberId = $this->memberId($request);

        if ($memberId === '') {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'expo_push_token' => ['required', 'string', 'max:255'],
        ]);

        NotifyDevice::query()
            ->where('member_id', $memberId)
            ->where('expo_push_token', $validated['expo_push_token'])
            ->update([
                'is_active' => false,
                'dtt_mod' => now(),
            ]);

        return response()->json([
            'message' => 'Notification device removed.',
        ]);
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
}
