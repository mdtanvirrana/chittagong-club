<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AffiliatedClub;
use App\Models\CircularItem;
use App\Models\NoticeMessage;
use App\Support\ClubFacilities;
use App\Support\CompanyProfile;
use App\Support\ContactDirectory;
use App\Support\GalleryAlbums;
use App\Support\PortalCache;
use App\Support\PortalContent;
use App\Support\PortalImageDirectory;
use App\Support\PortalPageImages;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClubContentController extends Controller
{
    public function gallery(): JsonResponse
    {
        $version = PortalCache::contentVersion('gallery');
        $albums = PortalCache::remember("api_gallery_albums_v2_{$version}", now()->addHours(6), function (): array {
            return GalleryAlbums::albums(false)->all();
        });

        return response()->json(['albums' => $albums]);
    }

    public function galleryPhotos(string $album): JsonResponse
    {
        $summary = GalleryAlbums::albumSummary($album);

        if (! $summary) {
            return response()->json(['message' => 'Album not found.'], 404);
        }

        $version = PortalCache::contentVersion('gallery');
        $photos = PortalCache::remember(
            'api_gallery_album_photos_'.$summary['id'].'_'.$summary['cache_key']."_v2_{$version}",
            now()->addHours(12),
            fn (): array => GalleryAlbums::albumPhotoPayloads($summary['id'])
        );

        return response()->json([
            'album' => $summary,
            'photos' => $photos,
        ]);
    }

    public function committee(Request $request): JsonResponse
    {
        $currentYear = (int) Carbon::now()->format('Y');
        $previousYear = $currentYear - 1;
        [$page, $perPage] = $this->paginationParams($request);

        $members = collect(PortalCache::remember(
            "api_committee_members_{$currentYear}_{$previousYear}_v1",
            now()->addMinutes(30),
            function () use ($currentYear, $previousYear): array {
                return DB::table('T_ORG_COMMITTEE as oc')
                    ->join('CustomerMst as c', 'oc.PrvcusID', '=', 'c.PrvCusID')
                    ->where('oc.is_active', 1)
                    ->where(function ($q) use ($currentYear, $previousYear) {
                        $q->where('oc.ct_from_year', $currentYear)
                            ->orWhere('oc.ct_from_year', $previousYear)
                            ->orWhere('oc.ct_to_year', $currentYear)
                            ->orWhere('oc.ct_to_year', $previousYear);
                    })
                    ->orderBy('oc.ct_from_year', 'desc')
                    ->orderBy('oc.id_serial')
                    ->select([
                        'oc.id_org_committee_key',
                        'oc.id_serial',
                        'oc.tx_designation',
                        'oc.tx_area',
                        'oc.ct_from_year',
                        'oc.ct_to_year',
                        'c.PrvCusID',
                        'c.CusName',
                        'c.Title',
                        'c.Mobile',
                        'c.Phone',
                    ])
                    ->get()
                    ->map(function (object $member): array {
                        $memberId = (string) $member->PrvCusID;
                        $name = trim(($member->Title ? $member->Title.' ' : '').$member->CusName);
                        $words = preg_split('/\s+/', trim((string) $member->CusName)) ?: [];

                        return [
                            'id' => (int) $member->id_org_committee_key,
                            'serial' => (int) $member->id_serial,
                            'name' => $name,
                            'initials' => collect(array_slice(array_filter($words), 0, 2))
                                ->map(fn (string $word): string => strtoupper(mb_substr($word, 0, 1)))
                                ->join(''),
                            'member_id' => $memberId,
                            'designation' => (string) ($member->tx_designation ?? ''),
                            'area' => (string) ($member->tx_area ?? ''),
                            'phone' => $member->Mobile ?: $member->Phone ?: null,
                            'year_from' => (int) $member->ct_from_year,
                            'year_to' => (int) $member->ct_to_year,
                            'has_photo' => PortalCache::hasMemberPhoto($memberId),
                            'photo_url' => PortalCache::memberPhotoUrl($memberId),
                            'photo_thumb_url' => PortalCache::memberPhotoThumbUrl($memberId),
                            'photo_preview_url' => PortalCache::memberPhotoPreviewUrl($memberId),
                        ];
                    })
                    ->values()
                    ->all();
            }
        ));

        $pageMembers = $members->forPage($page, $perPage)->values();

        $groups = $pageMembers
            ->groupBy(fn (array $member): string => $member['year_from'].'-'.$member['year_to'])
            ->map(fn ($group, string $key): array => [
                'key' => $key,
                'label' => str_replace('-', '-', $key),
                'members' => $group->values(),
            ])
            ->values();

        return response()->json([
            'current_year' => $currentYear,
            'previous_year' => $previousYear,
            'groups' => $groups,
            'total' => $members->count(),
            'pagination' => $this->paginationPayload($page, $perPage, $members->count(), $pageMembers->count()),
        ]);
    }

    public function employees(Request $request): JsonResponse
    {
        $version = PortalCache::contentVersion('employee-directory');
        [$page, $perPage] = $this->paginationParams($request);
        $search = trim((string) ($request->query('q') ?? $request->query('search') ?? ''));
        $branch = trim((string) $request->query('branch', ''));
        $cacheKey = sprintf(
            'api_employee_directory_page_%d_%d_%s_%s_v3_%d',
            $page,
            $perPage,
            md5(mb_strtolower($search)),
            md5(mb_strtolower($branch)),
            $version
        );

        $payload = PortalCache::remember($cacheKey, now()->addHours(6), function () use ($page, $perPage, $search, $branch): array {
            $query = DB::table('EmployeesDetails')
                ->where('PreStatus', 'Y')
                ->whereNotNull('EmpName');

            if ($search !== '') {
                $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';

                $query->where(function ($query) use ($like): void {
                    $query->where('EmpID', 'like', $like)
                        ->orWhere('EmpName', 'like', $like)
                        ->orWhere('Branch', 'like', $like)
                        ->orWhere('Sec', 'like', $like)
                        ->orWhere('Desig', 'like', $like);
                });
            }

            if ($branch !== '' && mb_strtolower($branch) !== 'all') {
                $query->where('Branch', $branch);
            }

            $total = (clone $query)->count();
            $employees = $query
                ->orderBy('Branch')
                ->orderBy('EmpName')
                ->select([
                    'EmpID',
                    'EmpName',
                    'Title',
                    'Branch',
                    'Sec',
                    'Desig',
                    'Mobile',
                    'BloodGroup',
                    'Sex',
                    'DateJoin',
                ])
                ->forPage($page, $perPage)
                ->get()
                ->map(function (object $employee): array {
                    $name = trim(($employee->Title && $employee->Title !== '0' ? $employee->Title.' ' : '').$employee->EmpName);
                    $words = preg_split('/\s+/', trim((string) $employee->EmpName)) ?: [];
                    $employeeId = (string) $employee->EmpID;

                    return [
                        'id' => $employeeId,
                        'name' => $name,
                        'initials' => collect(array_slice(array_filter($words), 0, 2))
                            ->map(fn (string $word): string => strtoupper(mb_substr($word, 0, 1)))
                            ->join(''),
                        'has_photo' => PortalCache::hasEmployeePhoto($employeeId),
                        'photo_url' => PortalCache::employeePhotoUrl($employeeId),
                        'photo_thumb_url' => PortalCache::employeePhotoThumbUrl($employeeId),
                        'photo_preview_url' => PortalCache::employeePhotoPreviewUrl($employeeId),
                        'branch' => (string) ($employee->Branch ?? ''),
                        'section' => ($employee->Sec && $employee->Sec !== $employee->Branch) ? (string) $employee->Sec : '',
                        'designation' => (string) ($employee->Desig ?? ''),
                        'phone' => ($employee->Mobile && $employee->Mobile !== '0') ? (string) $employee->Mobile : null,
                        'blood_group' => ($employee->BloodGroup && $employee->BloodGroup !== '0') ? (string) $employee->BloodGroup : '',
                        'sex' => (string) ($employee->Sex ?? ''),
                        'join_year' => $employee->DateJoin ? Carbon::parse($employee->DateJoin)->format('Y') : null,
                    ];
                })
                ->values()
                ->all();

            return [
                'employees' => $employees,
                'groups' => collect($employees)
                    ->groupBy(fn (array $employee): string => $employee['branch'] !== '' ? $employee['branch'] : 'Other')
                    ->map(fn ($members, string $branch): array => [
                        'branch' => $branch,
                        'members' => $members->values()->all(),
                    ])
                    ->values()
                    ->all(),
                'total' => $total,
                'pagination' => $this->paginationPayload($page, $perPage, $total, count($employees)),
            ];
        });

        $payload['branches'] = PortalCache::remember("api_employee_directory_branches_v1_{$version}", now()->addHours(6), function (): array {
            return DB::table('EmployeesDetails')
                ->where('PreStatus', 'Y')
                ->whereNotNull('EmpName')
                ->whereNotNull('Branch')
                ->select('Branch')
                ->distinct()
                ->orderBy('Branch')
                ->pluck('Branch')
                ->map(fn ($branch): string => trim((string) $branch))
                ->filter()
                ->values()
                ->all();
        });

        return response()->json($payload);
    }

    public function contact(): JsonResponse
    {
        $groups = ContactDirectory::publicDirectory();

        return response()->json([
            'groups' => $groups,
            'stats' => [
                'departments' => count($groups),
                'lines' => collect($groups)->sum('total_entries'),
                'emails' => collect($groups)->sum('email_count'),
            ],
        ]);
    }

    public function facilities(): JsonResponse
    {
        $facilities = ClubFacilities::all();

        return response()->json([
            'facilities' => $facilities,
            'total' => count($facilities),
        ]);
    }

    public function affiliatedClubs(Request $request): JsonResponse
    {
        [$page, $perPage] = $this->paginationParams($request);
        $version = PortalCache::contentVersion('affiliated-clubs');
        $clubs = PortalCache::remember("api_affiliated_clubs_v1_{$version}", now()->addMinutes(30), function (): array {
            $countryNames = $this->countryNamesByStoredValue();

            return AffiliatedClub::query()
                ->where('is_active', 1)
                ->select([
                    'id_affiliated_club_key',
                    'id_serial',
                    'COMPANY',
                    'Country',
                    'BranchName',
                    'BranchAddress',
                    'HOAddress',
                    'BranchTel',
                    'HOTel',
                    'tx_mobile',
                    'tx_email',
                    'tx_url',
                    'tx_fax',
                    'CEO',
                    'Logo_Path',
                    'image_path',
                ])
                ->get()
                ->map(function (AffiliatedClub $club) use ($countryNames): array {
                    $country = $this->resolveCountryName($club->getAttribute('Country'), $countryNames);
                    $hoAddress = trim((string) $club->getAttribute('HOAddress'));
                    $allPhones = collect();

                    foreach ([$club->BranchTel, $club->HOTel, $club->tx_mobile] as $source) {
                        if ($source) {
                            foreach (preg_split('/[,\/;]+/', $source) ?: [] as $phone) {
                                $phone = trim($phone);

                                if ($phone !== '') {
                                    $allPhones->push($phone);
                                }
                            }
                        }
                    }

                    $firstPhone = $allPhones->first();
                    $initials = collect(explode(' ', $club->display_name))
                        ->map(fn ($word): string => strtoupper($word[0] ?? ''))
                        ->take(2)
                        ->join('');

                    return [
                        'id' => (int) $club->id_affiliated_club_key,
                        'serial' => (int) $club->id_serial,
                        'name' => $club->display_name,
                        'country' => $country,
                        'branch' => (string) ($club->BranchName ?? ''),
                        'address' => $club->display_address ?? '',
                        'ho_address' => $hoAddress,
                        'initials' => $initials,
                        'first_phone' => $firstPhone ?: null,
                        'all_phones' => $allPhones->unique()->values()->all(),
                        'email' => (string) ($club->tx_email ?? ''),
                        'website' => (string) ($club->tx_url ?? ''),
                        'fax' => (string) ($club->tx_fax ?? ''),
                        'ceo' => (string) ($club->CEO ?? ''),
                        'logo_url' => $club->display_logo_url,
                        'image_url' => $club->display_image_url,
                    ];
                })
                ->sort(fn (array $a, array $b): int => $this->compareClubOrder($a, $b))
                ->values()
                ->all();
        });

        $pageClubs = collect($clubs)->forPage($page, $perPage)->values();

        $groups = $pageClubs
            ->groupBy(fn (array $club): string => $club['country'] ?: 'Country not set')
            ->map(fn ($group, string $country): array => [
                'country' => $country,
                'clubs' => $group->values(),
            ])
            ->values()
            ->all();

        return PortalCache::noStoreJson([
            'clubs' => $pageClubs,
            'groups' => $groups,
            'total' => count($clubs),
            'pagination' => $this->paginationPayload($page, $perPage, count($clubs), $pageClubs->count()),
        ]);
    }

    public function formerChairmen(Request $request): JsonResponse
    {
        [$page, $perPage] = $this->paginationParams($request);
        $members = collect(PortalCache::remember('api_former_chairmen_v1', now()->addMinutes(30), function (): array {
            return DB::table('T_ORG_COMMITTEE as oc')
                ->join('CustomerMst as c', 'oc.PrvcusID', '=', 'c.PrvCusID')
                ->where('oc.is_active', 1)
                ->whereRaw("LOWER(oc.tx_designation) LIKE 'chairman'")
                ->orderBy('oc.ct_from_year', 'desc')
                ->orderBy('oc.id_serial', 'desc')
                ->select([
                    'oc.id_org_committee_key',
                    'oc.id_serial',
                    'oc.tx_designation',
                    'oc.tx_area',
                    'oc.ct_from_year',
                    'oc.ct_to_year',
                    'c.PrvCusID',
                    'c.CusName',
                    'c.Title',
                    'c.Mobile',
                    'c.Phone',
                ])
                ->get()
                ->map(function (object $member): array {
                    $memberId = (string) $member->PrvCusID;
                    $name = trim(($member->Title ? $member->Title.' ' : '').$member->CusName);
                    $words = preg_split('/\s+/', trim((string) $member->CusName)) ?: [];

                    return [
                        'id' => (int) $member->id_org_committee_key,
                        'serial' => (int) $member->id_serial,
                        'name' => $name,
                        'initials' => collect(array_slice(array_filter($words), 0, 2))
                            ->map(fn (string $word): string => strtoupper(mb_substr($word, 0, 1)))
                            ->join(''),
                        'member_id' => $memberId,
                        'designation' => (string) ($member->tx_designation ?? ''),
                        'area' => (string) ($member->tx_area ?? ''),
                        'phone' => $member->Mobile ?: $member->Phone ?: null,
                        'year_from' => (int) $member->ct_from_year,
                        'year_to' => (int) $member->ct_to_year,
                        'has_photo' => PortalCache::hasMemberPhoto($memberId),
                        'photo_url' => PortalCache::memberPhotoUrl($memberId),
                        'photo_thumb_url' => PortalCache::memberPhotoThumbUrl($memberId),
                        'photo_preview_url' => PortalCache::memberPhotoPreviewUrl($memberId),
                    ];
                })
                ->values()
                ->all();
        }));

        $pageMembers = $members->forPage($page, $perPage)->values();

        $groups = $pageMembers
            ->groupBy(fn (array $member): string => $member['year_from'].'–'.$member['year_to'])
            ->map(fn ($group, string $label): array => [
                'label' => $label,
                'members' => $group->values(),
            ])
            ->values();

        return response()->json([
            'groups' => $groups,
            'total' => $members->count(),
            'pagination' => $this->paginationPayload($page, $perPage, $members->count(), $pageMembers->count()),
        ]);
    }

    public function clubInfoPage(string $page): JsonResponse
    {
        $folder = match ($page) {
            'dress-code' => PortalImageDirectory::DRESS_CODE_DIRECTORY,
            'general-rules' => PortalImageDirectory::GENERAL_RULES_DIRECTORY,
            default => null,
        };

        if ($folder === null) {
            return response()->json(['message' => 'Page not found.'], 404);
        }

        return response()->json([
            'page' => $page,
            'title' => $page === 'dress-code' ? 'Dress Code' : 'General Rules',
            'images' => PortalPageImages::urls($folder),
        ]);
    }

    public function legalPage(string $page): JsonResponse
    {
        $profile = CompanyProfile::current();
        $contactGroups = [];
        $contactStats = null;

        if ($page === 'contact') {
            $contactGroups = ContactDirectory::publicDirectory();
            $contactStats = [
                'departments' => count($contactGroups),
                'lines' => collect($contactGroups)->sum('total_entries'),
                'emails' => collect($contactGroups)->sum('email_count'),
            ];
        }

        return response()->json([
            'page' => $page,
            ...$this->legalPageData($page, $profile),
            'contact_groups' => $contactGroups,
            'contact_stats' => $contactStats,
        ]);
    }

    public function circulars(Request $request): JsonResponse
    {
        [$page, $perPage] = $this->paginationParams($request);
        $version = PortalCache::contentVersion('circulars');
        $cacheKey = "api_circulars_page_{$page}_{$perPage}_v2_{$version}";

        $payload = PortalCache::rememberResilient(
            $cacheKey,
            PortalCache::staleKey($cacheKey),
            now()->addMinutes(5),
            now()->addDay(),
            function () use ($page, $perPage): array {
                $query = CircularItem::query()->visible();
                $total = (clone $query)->count();
                $items = $query
                    ->orderByDesc('dtt_ad_start')
                    ->orderByDesc('id_career_key')
                    ->forPage($page, $perPage)
                    ->get()
                    ->map(fn (CircularItem $circular): array => [
                        'id' => (int) $circular->id_career_key,
                        'title' => trim((string) ($circular->tx_title ?: 'Circular')),
                        'body' => $circular->body_text,
                        'excerpt' => $circular->excerpt,
                        'image_url' => $circular->image_url,
                        'display_image_url' => $circular->display_image_url,
                        'image_thumb_url' => $circular->display_image_thumb_url,
                        'source_url' => $circular->action_url,
                        'start_date' => $circular->start_date_label,
                        'close_date' => $circular->has_distinct_close_date ? $circular->close_date_label : null,
                        'date_label' => $circular->has_distinct_close_date ? $circular->close_date_label : $circular->start_date_label,
                        'uploaded_date' => $circular->dtt_added?->format('M d, Y') ?? 'Unknown',
                    ])
                    ->values();

                return [
                    'circulars' => $items,
                    'total' => $total,
                    'pagination' => $this->paginationPayload($page, $perPage, $total, $items->count()),
                ];
            },
            [
                'circulars' => [],
                'total' => 0,
                'pagination' => $this->paginationPayload($page, $perPage, 0, 0),
            ]
        );

        return response()->json($payload);
    }

    public function notices(Request $request): JsonResponse
    {
        [$page, $perPage] = $this->paginationParams($request);
        $version = PortalCache::contentVersion('notices');
        $cacheKey = "api_notices_page_{$page}_{$perPage}_v2_{$version}";

        $payload = PortalCache::rememberResilient(
            $cacheKey,
            PortalCache::staleKey($cacheKey),
            now()->addMinutes(5),
            now()->addDay(),
            function () use ($page, $perPage): array {
                $query = NoticeMessage::query()->visible();
                $total = (clone $query)->count();
                $items = $query
                    ->orderByDesc('Edate')
                    ->orderByDesc('id_message_key')
                    ->forPage($page, $perPage)
                    ->get()
                    ->map(fn (NoticeMessage $notice): array => [
                        'id' => (int) $notice->id_message_key,
                        'title' => trim((string) ($notice->tx_title ?: 'Notice')),
                        'body' => $notice->body_text,
                        'excerpt' => $notice->excerpt,
                        'date' => $notice->published_date_label,
                        'date_sort' => $notice->Edate?->format('Y-m-d') ?? '',
                    ])
                    ->values();

                return [
                    'notices' => $items,
                    'total' => $total,
                    'pagination' => $this->paginationPayload($page, $perPage, $total, $items->count()),
                ];
            },
            [
                'notices' => [],
                'total' => 0,
                'pagination' => $this->paginationPayload($page, $perPage, 0, 0),
            ]
        );

        return response()->json($payload);
    }

    private function paginationParams(Request $request): array
    {
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = min(max((int) $request->integer('per_page', $request->integer('limit', 20)), 1), 20);

        return [$page, $perPage];
    }

    private function paginationPayload(int $page, int $perPage, int $total, int $pageCount): array
    {
        $lastPage = max(1, (int) ceil($total / max(1, $perPage)));
        $from = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;

        return [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
            'has_more' => $page < $lastPage,
            'from' => $from,
            'to' => $from === 0 ? 0 : min($from + $pageCount - 1, $total),
        ];
    }

    private function countryNamesByStoredValue(): array
    {
        try {
            return DB::table('ACountry')
                ->select(['CID', 'CName'])
                ->get()
                ->reduce(function (array $names, object $country): array {
                    $name = $this->normalizeCountryValue($country->CName ?? null);

                    if ($name === '') {
                        return $names;
                    }

                    foreach ([$country->CID ?? null, $name] as $value) {
                        $key = $this->countryLookupKey($value);

                        if ($key !== '') {
                            $names[$key] = $name;
                        }
                    }

                    return $names;
                }, []);
        } catch (\Throwable) {
            return [];
        }
    }

    private function resolveCountryName(mixed $country, array $countryNames): string
    {
        $value = $this->normalizeCountryValue($country);

        if ($value === '' || $value === '0' || $value === '?') {
            return 'Country not set';
        }

        return $countryNames[$this->countryLookupKey($value)] ?? $value;
    }

    private function normalizeCountryValue(mixed $country): string
    {
        return preg_replace('/\s+/', ' ', trim((string) $country)) ?? '';
    }

    private function countryLookupKey(mixed $country): string
    {
        return strtolower($this->normalizeCountryValue($country));
    }

    private function compareClubOrder(array $a, array $b): int
    {
        $aKey = $this->clubOrderKey($a);
        $bKey = $this->clubOrderKey($b);

        foreach ($aKey as $index => $value) {
            $comparison = $value <=> $bKey[$index];

            if ($comparison !== 0) {
                return $comparison;
            }
        }

        return 0;
    }

    private function clubOrderKey(array $club): array
    {
        $country = $this->normalizeCountryValue($club['country'] ?? '');

        return [
            strcasecmp($country, 'Country not set') === 0 ? 1 : 0,
            strtolower($country),
            strtolower($this->normalizeCountryValue($club['name'] ?? '')),
            (int) ($club['id'] ?? 0),
        ];
    }

    private function legalPageData(string $pageKey, array $profile): array
    {
        return match ($pageKey) {
            'terms' => [
                'pageTitle' => 'Terms & Conditions',
                'pageDescription' => 'Member eligibility, ERP integration, and online bill payment terms for the club app.',
                'sections' => [
                    [
                        'title' => 'Member Eligibility',
                        'icon' => 'verified_user',
                        'body' => [
                            'Only '.$profile['companyName'].' ('.$profile['shortName'].') active members are eligible to access this club app and pay their bills through the SSLCOMMERZ payment gateway.',
                        ],
                    ],
                    [
                        'title' => 'ERP Integration',
                        'icon' => 'hub',
                        'body' => [
                            'This app is fully integrated with Club ERP Software.',
                            'Registered '.$profile['shortName'].' members\' club ID, mobile number, bills, and related information are shown from ERP software and maintained by Club ERP Software.',
                        ],
                    ],
                    [
                        'title' => 'Payment Confirmation',
                        'icon' => 'sms',
                        'body' => [
                            'After completing an online payment, a member will receive a SMS at the registered mobile number and an email at the registered email address.',
                        ],
                    ],
                ],
            ],
            'privacy' => [
                'pageTitle' => 'Privacy Policy',
                'pageDescription' => 'How member information is presented, used, and protected inside the club app.',
                'sections' => [
                    [
                        'title' => 'About the Club',
                        'icon' => 'apartment',
                        'body' => array_values(array_filter([
                            $profile['companyName'].' ('.$profile['shortName'].') is a non-profit organization which is run and managed by the elected General Committee Members',
                            $profile['contactSummary'] !== '' ? $profile['contactSummary'] : null,
                            $profile['companyAddressText'] !== '' ? 'Address: '.$profile['companyAddressText'] : null,
                        ])),
                    ],
                    [
                        'title' => 'Usage of Information',
                        'icon' => 'manage_accounts',
                        'body' => [
                            'Only registered '.$profile['shortName'].' club members are eligible to access their information.',
                        ],
                    ],
                    [
                        'title' => 'Third-Party Policy',
                        'icon' => 'policy',
                        'body' => [
                            'Third parties are not allowed to use this app. Only registered '.$profile['shortName'].' club members are eligible to access their information among themselves.',
                        ],
                    ],
                    [
                        'title' => 'Information Security',
                        'icon' => 'shield_lock',
                        'body' => [
                            'To protect your personal information, reasonable measures and industry-standard practices are adopted to ensure information is not misused, accessed, disclosed, altered, or destroyed.',
                            'If you provide credit card information, it is encrypted using secure socket layer technology (SSL) by SSLCOMMERZ.',
                        ],
                    ],
                ],
            ],
            'refund' => [
                'pageTitle' => 'Return and Refund Policy',
                'pageDescription' => 'Online payments completed through the club app are treated as final transactions.',
                'sections' => [
                    [
                        'title' => 'Refund Terms',
                        'icon' => 'payments',
                        'body' => ['All types of payment through this app are non-refundable.'],
                    ],
                ],
            ],
            'data' => [
                'pageTitle' => 'Data Policy',
                'pageDescription' => 'How the club stores member data for operations, communication, and member services.',
                'sections' => [
                    [
                        'title' => 'Member Data',
                        'icon' => 'database',
                        'body' => [
                            $profile['companyName'].' ('.$profile['shortName'].') member data is collected and stored securely for the purpose of membership management, communication, event invitations, and other club facilities.',
                        ],
                    ],
                    [
                        'title' => 'Sharing Policy',
                        'icon' => 'lock_person',
                        'body' => [
                            'Personal information is not shared with third parties. Only registered '.$profile['shortName'].' club members are eligible to access their information among themselves.',
                        ],
                    ],
                ],
            ],
            'contact' => [
                'pageTitle' => 'Contact Us',
                'pageDescription' => 'Reach the club office and browse the live contact directory published from the club system.',
                'sections' => [
                    [
                        'title' => 'Club Information',
                        'icon' => 'support_agent',
                        'body' => array_values(array_filter([
                            $profile['companyName'].' is a not-for-profit organization which is run by, and for, its members.',
                            $profile['contactSummary'] !== '' ? $profile['contactSummary'] : null,
                            $profile['companyAddressText'] !== '' ? 'Address: '.$profile['companyAddressText'] : null,
                        ])),
                    ],
                ],
            ],
            default => abort(404),
        };
    }
}
