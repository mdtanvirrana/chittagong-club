<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\MemberAccess;
use App\Support\MemberProfileViewData;
use App\Support\PortalCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberDirectoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $version = PortalCache::contentVersion('member-directory');
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = min(max((int) $request->integer('per_page', $request->integer('limit', 20)), 1), 20);
        $search = trim((string) ($request->query('q') ?? $request->query('search') ?? ''));
        $cacheKey = sprintf(
            'api_member_directory_page_%d_%d_%s',
            $page,
            $perPage,
            md5(mb_strtolower($search))
        );

        $payload = PortalCache::rememberGlobal($cacheKey, now()->addHours(6), function () use ($page, $perPage, $search): array {
            $query = MemberAccess::activeMemberQuery()
                ->when($search !== '', function ($query) use ($search): void {
                    $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';

                    $query->where(function ($query) use ($like): void {
                        $query->where('c.PrvCusID', 'like', $like)
                            ->orWhere('c.CusName', 'like', $like)
                            ->orWhere('c.Mobile', 'like', $like)
                            ->orWhere('cc.Remarks', 'like', $like);
                    });
                });
            $total = (clone $query)->count();
            $members = $query
                ->orderBy('c.Cardid')
                ->orderBy('c.slno')
                ->select('c.PrvCusID', 'c.CusName', 'c.Email', 'c.Mobile', 'cc.Remarks as MemberCategory')
                ->forPage($page, $perPage)
                ->get()
                ->map(fn (object $member): array => $this->memberListPayload($member))
                ->values()
                ->all();

            return [
                'members' => $members,
                'total' => $total,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => max(1, (int) ceil($total / $perPage)),
                    'has_more' => ($page * $perPage) < $total,
                    'from' => $total === 0 ? 0 : (($page - 1) * $perPage) + 1,
                    'to' => $total === 0 ? 0 : min($page * $perPage, $total),
                ],
            ];
        }, "v2-{$version}");

        return response()->json($payload);
    }

    public function show(string $id): JsonResponse
    {
        $data = MemberProfileViewData::forMemberId($id);
        $member = $data['member'];

        return response()->json([
            'member' => [
                'id' => (string) $member->PrvCusID,
                'full_name' => $data['fullName'],
                'initials' => $data['initials'],
                'category' => (string) ($member->MemberCategory ?? ''),
                'status' => (string) ($member->MemExpTypeName ?? ''),
                'birth_date' => $data['birthDate'],
                'age' => $data['age'],
                'join_date' => $data['joinDate'],
                'join_year' => $member->DOE ? (string) \Carbon\Carbon::parse($member->DOE)->format('Y') : '—',
                'expiry_date' => $member->ExpDt ? \Carbon\Carbon::parse($member->ExpDt)->format('M d, Y') : '—',
                'blood_group' => (string) ($member->BloodGroup ?? ''),
                'gender' => match (strtolower((string) ($member->Sex ?? ''))) {
                    'm' => 'Male',
                    'f' => 'Female',
                    default => (string) ($member->Sex ?? ''),
                },
                'religion' => (string) ($member->Religion ?? ''),
                'nationality' => (string) ($member->Nationality ?? ''),
                'mobile' => (string) ($member->Mobile ?? ''),
                'phone' => (string) ($member->Phone ?? ''),
                'email' => (string) ($member->Email ?? ''),
                'address' => (string) ($member->Address ?? ''),
                'profession' => (string) ($member->Profession ?? ''),
                'company' => (string) ($member->ComName ?? ''),
                'marital_status' => $data['isMarried'] ? 'Married' : (string) ($member->MaritalStatus ?? ''),
                'father_name' => (string) ($member->FatherName ?? ''),
                'mother_name' => (string) ($member->MotherName ?? ''),
                'children' => $data['children'],
                'children_count' => $data['childrenCount'],
                'has_more_children' => $data['hasMoreChildren'],
                'is_married' => $data['isMarried'],
                'spouse' => [
                    'name' => (string) ($member->SpouseName ?? ''),
                    'blood_group' => (string) ($member->SpoBlood ?? ''),
                    'mobile' => (string) ($member->SpoMobile ?? ''),
                    'wedding_date' => $data['weddingDt'],
                ],
                'has_photo' => $data['hasProfilePhoto'],
                'photo_url' => $data['profilePhotoUrl'],
                'photo_thumb_url' => $data['profilePhotoThumbUrl'],
                'photo_preview_url' => $data['profilePhotoPreviewUrl'],
                'call_href' => $data['callHref'],
                'sms_href' => $data['smsHref'],
                'email_href' => $data['emailHref'],
            ],
        ]);
    }

    private function memberListPayload(object $member): array
    {
        $memberId = (string) $member->PrvCusID;
        $words = array_values(array_filter(explode(' ', trim((string) $member->CusName))));

        return [
            'id' => $memberId,
            'name' => (string) $member->CusName,
            'category' => (string) ($member->MemberCategory ?? ''),
            'initials' => implode('', array_map(
                fn (string $word): string => strtoupper(mb_substr($word, 0, 1)),
                array_slice($words, 0, 2)
            )),
            'has_photo' => PortalCache::hasMemberPhoto($memberId),
            'photo_url' => PortalCache::memberPhotoUrl($memberId),
            'photo_thumb_url' => PortalCache::memberPhotoThumbUrl($memberId),
            'photo_preview_url' => PortalCache::memberPhotoPreviewUrl($memberId),
            'email' => trim((string) ($member->Email ?? '')),
            'mobile' => trim((string) ($member->Mobile ?? '')),
        ];
    }
}
