<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\CircularItem;
use App\Models\NoticeMessage;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'admins' => AdminUser::query()->count(),
            'notices_total' => NoticeMessage::query()->count(),
            'notices_published' => NoticeMessage::query()->visible()->count(),
            'circulars_total' => CircularItem::query()->count(),
            'circulars_published' => CircularItem::query()->visible()->count(),
        ];

        $recentNotices = NoticeMessage::query()
            ->orderByDesc('Edate')
            ->orderByDesc('id_message_key')
            ->limit(5)
            ->get();

        $recentCirculars = CircularItem::query()
            ->orderByDesc('dtt_ad_start')
            ->orderByDesc('id_career_key')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentNotices', 'recentCirculars'));
    }
}
