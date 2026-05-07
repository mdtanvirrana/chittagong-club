<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\NoticeMessage;
use App\Models\CircularItem;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'admins' => AdminUser::query()
                ->where('is_admin', 1)
                ->count(),
            'notices_total' => NoticeMessage::query()->count(),
            'notices_published' => NoticeMessage::query()->visible()->count(),
            'circulars_total' => CircularItem::query()->count(),
            'circulars_published' => CircularItem::query()->visible()->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
