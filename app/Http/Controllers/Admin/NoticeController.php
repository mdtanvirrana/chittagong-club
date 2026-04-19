<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NoticeRequest;
use App\Models\NoticeMessage;
use App\Support\PortalContent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q', ''));

        $notices = NoticeMessage::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('tx_title', 'like', "%{$search}%")
                        ->orWhere('tx_post_mgs', 'like', "%{$search}%")
                        ->orWhere('id_message_key', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('Edate')
            ->orderByDesc('id_message_key')
            ->paginate(12)
            ->withQueryString();

        return view('admin.notices.index', compact('notices', 'search'));
    }

    public function create()
    {
        return view('admin.notices.form', [
            'notice' => new NoticeMessage([
                'Edate' => now()->startOfDay(),
                'Etime' => now()->format('H:i:s'),
                'is_active' => true,
                'is_online' => true,
            ]),
            'isEditing' => false,
        ]);
    }

    public function store(NoticeRequest $request)
    {
        DB::transaction(function () use ($request) {
            $now = now();
            $nextId = ((int) (NoticeMessage::query()->lockForUpdate()->max('id_message_key') ?? 100000)) + 1;
            $userId = $this->resolveAuditUserId();

            NoticeMessage::query()->create([
                'id_message_key' => $nextId,
                'id_message_ver' => 1,
                'is_active' => $request->boolean('is_active'),
                'id_ds_env' => 100000,
                'dtt_mod' => $now,
                'id_user_mod' => $userId,
                'id_env_key' => 100000,
                'id_event_key' => 1,
                'id_state_key' => 100000,
                'id_action_key' => 100000,
                'dtt_added' => $now,
                'Edate' => Carbon::parse($request->input('publish_date'))->toDateString(),
                'Etime' => $request->input('publish_time').':00',
                'is_online' => $request->boolean('is_online'),
                'tx_img_src' => $this->resolveMetadataField($request, 'image_url'),
                'tx_post_url' => $this->resolveMetadataField($request, 'post_url'),
                'tx_mac' => '?',
                'tx_ip6' => $this->resolveIpv6($request),
                'tx_ip4' => $this->resolveIpv4($request),
                'tx_title' => trim((string) $request->input('title')),
                'tx_post_mgs' => PortalContent::plainTextToDelta($request->input('body')),
                'id_user_key' => $userId,
                'tx_comment' => $this->resolveMetadataField($request, 'comment'),
            ]);
        });

        PortalContent::clearNoticeCaches();

        return redirect()
            ->route('admin.notices.index')
            ->with('status', 'Notice created successfully.');
    }

    public function edit(int $notice)
    {
        return view('admin.notices.form', [
            'notice' => $this->findNotice($notice),
            'isEditing' => true,
        ]);
    }

    public function update(NoticeRequest $request, int $notice)
    {
        $noticeRow = $this->findNotice($notice);

        $noticeRow->fill([
            'id_message_ver' => max(1, (int) $noticeRow->id_message_ver) + 1,
            'is_active' => $request->boolean('is_active'),
            'dtt_mod' => now(),
            'id_user_mod' => $this->resolveAuditUserId(),
            'Edate' => Carbon::parse($request->input('publish_date'))->toDateString(),
            'Etime' => $request->input('publish_time').':00',
            'is_online' => $request->boolean('is_online'),
            'tx_img_src' => $this->resolveMetadataField($request, 'image_url', $noticeRow->tx_img_src),
            'tx_post_url' => $this->resolveMetadataField($request, 'post_url', $noticeRow->tx_post_url),
            'tx_title' => trim((string) $request->input('title')),
            'tx_post_mgs' => PortalContent::plainTextToDelta($request->input('body')),
            'tx_comment' => $this->resolveMetadataField($request, 'comment', $noticeRow->tx_comment),
            'id_user_key' => $this->resolveAuditUserId(),
        ])->save();

        PortalContent::clearNoticeCaches();

        return redirect()
            ->route('admin.notices.index')
            ->with('status', 'Notice updated successfully.');
    }

    public function toggleOnline(int $notice)
    {
        $noticeRow = $this->findNotice($notice);
        $noticeRow->fill([
            'is_online' => ! (bool) $noticeRow->is_online,
            'id_message_ver' => max(1, (int) $noticeRow->id_message_ver) + 1,
            'dtt_mod' => now(),
            'id_user_mod' => $this->resolveAuditUserId(),
        ])->save();

        PortalContent::clearNoticeCaches();

        return back()->with('status', 'Notice visibility updated.');
    }

    public function toggleActive(int $notice)
    {
        $noticeRow = $this->findNotice($notice);
        $noticeRow->fill([
            'is_active' => ! (bool) $noticeRow->is_active,
            'id_message_ver' => max(1, (int) $noticeRow->id_message_ver) + 1,
            'dtt_mod' => now(),
            'id_user_mod' => $this->resolveAuditUserId(),
        ])->save();

        PortalContent::clearNoticeCaches();

        return back()->with('status', 'Notice status updated.');
    }

    private function findNotice(int $noticeId): NoticeMessage
    {
        return NoticeMessage::query()->findOrFail($noticeId);
    }

    private function resolveAuditUserId(): int
    {
        $identifier = Auth::guard('admin')->user()?->userid;

        return is_numeric($identifier) ? (int) $identifier : 0;
    }

    private function resolveIpv4(Request $request): string
    {
        $ip = (string) $request->ip();

        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? $ip : '?';
    }

    private function resolveIpv6(Request $request): string
    {
        $ip = (string) $request->ip();

        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? $ip : '?';
    }

    private function resolveMetadataField(Request $request, string $key, ?string $currentValue = null): ?string
    {
        if ($request->exists($key)) {
            return PortalContent::optionalField($request->input($key));
        }

        return $currentValue ?? '?';
    }
}
