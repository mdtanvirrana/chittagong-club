<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = DB::table('T_MESSAGE')
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
                // Parse the Quill delta JSON into plain text
                $plainText = '';
                $boldParts = [];

                if ($row->tx_post_mgs) {
                    $decoded = json_decode($row->tx_post_mgs, true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $op) {
                            if (isset($op['insert']) && is_string($op['insert'])) {
                                $plainText .= $op['insert'];
                                // Collect bold segments for excerpt highlighting
                                if (!empty($op['attributes']['bold'])) {
                                    $boldParts[] = trim($op['insert']);
                                }
                            }
                        }
                    }
                }

                $plainText = trim($plainText);

                // Excerpt: first 120 chars of plain text
                $excerpt = mb_strlen($plainText) > 120
                    ? mb_substr($plainText, 0, 120) . '…'
                    : $plainText;
                // Just the first line for excerpt
                $firstLine = trim(explode("\n", $plainText)[0] ?? $excerpt);
                $excerpt   = mb_strlen($firstLine) > 120
                    ? mb_substr($firstLine, 0, 120) . '…'
                    : $firstLine;

                return [
                    'id'        => $row->id_message_key,
                    'title'     => $row->tx_title,
                    'body'      => $plainText,       // full text for modal
                    'excerpt'   => $excerpt,
                    'date'      => Carbon::parse($row->Edate)->format('M d, Y'),
                    'date_sort' => $row->Edate,
                ];
            });

        return view('pages.notice-board', compact('notices'));
    }
}