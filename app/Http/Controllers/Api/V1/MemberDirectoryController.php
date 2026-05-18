<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\MemberAccess;
use App\Support\MemberProfileViewData;
use App\Support\PortalCache;
use Illuminate\Http\JsonResponse;

class MemberDirectoryController extends Controller
{
    public function index(): JsonResponse
    {
        $members = PortalCache::rememberGlobal('api_member_directory', now()->addSeconds(PortalCache::ttl('global')), function (): array {
            return MemberAccess::activeMemberQuery()
                ->orderBy('c.Cardid')
                ->orderBy('c.slno')
                ->select('c.PrvCusID', 'c.CusName', 'c.Email', 'c.Mobile', 'cc.Remarks as MemberCategory')
                ->get()
                ->map(function (object $member): array {
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
                        'email' => trim((string) ($member->Email ?? '')),
                        'mobile' => trim((string) ($member->Mobile ?? '')),
                    ];
                })
                ->values()
                ->all();
        });

        return response()->json([
            'members' => $members,
            'total' => count($members),
        ]);
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
                'call_href' => $data['callHref'],
                'sms_href' => $data['smsHref'],
                'email_href' => $data['emailHref'],
            ],
        ]);
    }
}
