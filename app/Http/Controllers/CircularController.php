<?php

namespace App\Http\Controllers;

use App\Models\CircularItem;
use App\Support\PortalCache;
use App\Support\PortalContent;
use Illuminate\Pagination\LengthAwarePaginator;

class CircularController extends Controller
{
    public function index()
    {
        $perPage = 10;
        $page = max(1, (int) request()->query('page', 1));

        $items = collect(PortalCache::rememberResilient(
            PortalContent::CIRCULAR_CACHE_KEY,
            PortalContent::CIRCULAR_STALE_CACHE_KEY,
            now()->addMinutes(5),
            now()->addDay(),
            function (): array {
                return CircularItem::query()
                    ->visible()
                    ->orderByDesc('dtt_ad_start')
                    ->orderByDesc('id_career_key')
                    ->get()
                    ->map(function (CircularItem $circular) {
                        return [
                            'id' => (int) $circular->id_career_key,
                            'title' => trim((string) ($circular->tx_title ?: 'Circular')),
                            'body' => $circular->body_text,
                            'excerpt' => $circular->excerpt,
                            'image_url' => $circular->image_url,
                            'display_image_url' => $circular->display_image_url,
                            'source_url' => $circular->action_url,
                            'start_date' => $circular->start_date_label,
                            'close_date' => $circular->has_distinct_close_date ? $circular->close_date_label : null,
                            'date_label' => $circular->has_distinct_close_date ? $circular->close_date_label : $circular->start_date_label,
                            'uploaded_date' => $circular->dtt_added?->format('M d, Y') ?? 'Unknown',
                            'is_online' => (bool) $circular->is_online,
                        ];
                    })
                    ->values()
                    ->all();
            },
            []
        ));

        $circulars = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('pages.circulars', compact('circulars'));
    }
}
