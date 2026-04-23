<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Support\MemberAccess;
use App\Support\PortalCache;

class MemberDirectoryController extends Controller
{
    public function index()
    {
        $members = PortalCache::remember('member_directory_v4', now()->addMinutes(15), function (): array {
            return MemberAccess::activeMemberQuery()
                ->orderBy('c.Cardid')
                ->orderBy('c.slno')
                ->select('c.PrvCusID', 'c.CusName', 'c.Email', 'c.Mobile', 'cc.Remarks as MemberCategory')
                ->get()
                ->map(function ($m) {
                    $memberId = (string) $m->PrvCusID;
                    $words = array_values(array_filter(explode(' ', trim($m->CusName))));
                    $initials = implode('', array_map(
                        fn ($w) => strtoupper(mb_substr($w, 0, 1)),
                        array_slice($words, 0, 2)
                    ));
                    $hasPhoto = PortalCache::hasMemberPhoto($memberId);

                    return [
                        'id' => $memberId,
                        'name' => (string) $m->CusName,
                        'category' => (string) ($m->MemberCategory ?? ''),
                        'initials' => $initials,
                        'has_photo' => $hasPhoto,
                        'photo_url' => PortalCache::memberPhotoUrl($memberId),
                        'email' => trim((string) ($m->Email ?? '')),
                        'mobile' => trim((string) ($m->Mobile ?? '')),
                    ];
                })
                ->values()
                ->all();
        });

        // Safely encode — HEX flags prevent ANY special char from breaking Blade/JS
        $membersJson = json_encode(
            $members,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE
        );

        return view('pages.member-directory', [
            'membersJson' => $membersJson,
            'total' => count($members),
        ]);
    }
}
