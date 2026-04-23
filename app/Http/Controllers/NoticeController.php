<?php

namespace App\Http\Controllers;

use App\Models\NoticeMessage;
use App\Support\PortalCache;
use App\Support\PortalContent;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = collect(PortalCache::rememberResilient(
            PortalContent::NOTICE_CACHE_KEY,
            PortalContent::NOTICE_STALE_CACHE_KEY,
            now()->addMinutes(5),
            now()->addDay(),
            function (): array {
                return NoticeMessage::query()
                    ->visible()
                    ->orderByDesc('Edate')
                    ->orderByDesc('id_message_key')
                    ->get()
                    ->map(function (NoticeMessage $notice) {
                        return [
                            'id' => (int) $notice->id_message_key,
                            'title' => trim((string) ($notice->tx_title ?: 'Notice')),
                            'body' => $notice->body_text,
                            'excerpt' => $notice->excerpt,
                            'date' => $notice->published_date_label,
                            'date_sort' => $notice->Edate?->format('Y-m-d') ?? '',
                        ];
                    })
                    ->values()
                    ->all();
            },
            []
        ));

        return view('pages.notice-board', compact('notices'));
    }
}
