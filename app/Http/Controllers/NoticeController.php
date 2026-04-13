<?php

namespace App\Http\Controllers;

use App\Support\PortalCache;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = collect(PortalCache::remember('notice_board_v1', now()->addMinutes(10), function (): array {
            return DB::table('T_MESSAGE')
                ->where('is_active', 1)
                ->whereNotNull('tx_title')
                ->orderBy('Edate', 'desc')
                ->select([
                    'id_message_key',
                    'tx_title',
                    'tx_post_mgs',
                    'Edate',
                    'is_online',
                ])
                ->get()
                ->map(function ($row) {
                    $plainText = '';

                    if ($row->tx_post_mgs) {
                        $decoded = json_decode($row->tx_post_mgs, true);
                        if (is_array($decoded)) {
                            foreach ($decoded as $op) {
                                if (isset($op['insert']) && is_string($op['insert'])) {
                                    $plainText .= $op['insert'];
                                }
                            }
                        }
                    }

                    $plainText = trim($plainText);
                    $excerpt = mb_strlen($plainText) > 120
                        ? mb_substr($plainText, 0, 120) . '…'
                        : $plainText;
                    $firstLine = trim(explode("\n", $plainText)[0] ?? $excerpt);
                    $excerpt = mb_strlen($firstLine) > 120
                        ? mb_substr($firstLine, 0, 120) . '…'
                        : $firstLine;

                    return [
                        'id' => $row->id_message_key,
                        'title' => $row->tx_title,
                        'body' => $plainText,
                        'excerpt' => $excerpt,
                        'date' => Carbon::parse($row->Edate)->format('M d, Y'),
                        'date_sort' => $row->Edate,
                    ];
                })
                ->values()
                ->all();
        }));

        return view('pages.notice-board', compact('notices'));
    }
}
