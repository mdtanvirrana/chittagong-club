<?php

namespace App\Support;

use App\Models\ClubContact;
use Illuminate\Support\Facades\DB;

class ContactDirectory
{
    private const HIERARCHY_CACHE_KEY = 'contact_department_hierarchy_v1';

    private const PUBLIC_CACHE_KEY = 'contact_directory_public_v1';

    private const PUBLIC_STALE_CACHE_KEY = 'contact_directory_public_stale_v1';

    public static function departmentHierarchy(): array
    {
        return PortalCache::remember(self::HIERARCHY_CACHE_KEY, now()->addHour(), function (): array {
            $hierarchy = [];

            DB::table('List_Department')
                ->select(['DepartmentNameMaster', 'Departmentname'])
                ->whereNotNull('DepartmentNameMaster')
                ->whereRaw("LTRIM(RTRIM(DepartmentNameMaster)) <> ''")
                ->whereRaw("LTRIM(RTRIM(DepartmentNameMaster)) <> '0'")
                ->whereRaw("LEN(LTRIM(RTRIM(DepartmentNameMaster))) <= 20")
                ->orderBy('DepartmentNameMaster')
                ->orderBy('Departmentname')
                ->get()
                ->each(function ($row) use (&$hierarchy): void {
                    $department = static::clean($row->DepartmentNameMaster);
                    $subDepartment = static::clean($row->Departmentname);

                    if ($department === '') {
                        return;
                    }

                    $hierarchy[$department] ??= [];

                    if ($subDepartment !== '' && ! in_array($subDepartment, $hierarchy[$department], true)) {
                        $hierarchy[$department][] = $subDepartment;
                    }
                });

            ClubContact::query()
                ->orderBy('Sl')
                ->get(['Contact_Dept', 'Contact_Sub_Dept'])
                ->each(function (ClubContact $contact) use (&$hierarchy): void {
                    $department = $contact->department_name;

                    if ($department === '') {
                        return;
                    }

                    $hierarchy[$department] ??= [];

                    $subDepartment = $contact->sub_department_name;

                    if ($subDepartment !== null && ! in_array($subDepartment, $hierarchy[$department], true)) {
                        $hierarchy[$department][] = $subDepartment;
                    }
                });

            ksort($hierarchy, SORT_NATURAL | SORT_FLAG_CASE);

            foreach ($hierarchy as &$subDepartments) {
                sort($subDepartments, SORT_NATURAL | SORT_FLAG_CASE);
            }

            unset($subDepartments);

            return $hierarchy;
        });
    }

    public static function departmentOptions(): array
    {
        return array_keys(static::departmentHierarchy());
    }

    public static function subDepartmentOptions(?string $department): array
    {
        $department = static::clean($department);

        if ($department === '') {
            return [];
        }

        return static::departmentHierarchy()[$department] ?? [];
    }

    public static function publicDirectory(): array
    {
        return PortalCache::rememberResilient(
            self::PUBLIC_CACHE_KEY,
            self::PUBLIC_STALE_CACHE_KEY,
            now()->addMinutes(20),
            now()->addDay(),
            fn (): array => static::buildGroups(ClubContact::query()->orderBy('Sl')->get()),
            []
        );
    }

    public static function buildGroups(iterable $contacts): array
    {
        $groups = [];

        foreach ($contacts as $contact) {
            $department = static::clean($contact->Contact_Dept ?? $contact->department_name ?? null);
            $subDepartment = static::nullable(static::clean($contact->Contact_Sub_Dept ?? $contact->sub_department_name ?? null));
            $phone = static::nullable(static::clean($contact->Phone ?? $contact->phone_number ?? null));
            $email = static::nullable(static::clean($contact->Email ?? $contact->email_address ?? null));
            $groupId = static::parseNumeric(static::clean($contact->Contact_ID ?? $contact->group_id ?? null));
            $sl = static::parseNumeric((string) ($contact->Sl ?? $contact->sl ?? 0)) ?? 0;

            if ($department === '' || ($phone === null && $email === null)) {
                continue;
            }

            $groupKey = ($groupId !== null ? 'group:' . $groupId : 'dept:' . strtolower($department))
                . '|'
                . strtolower($department);

            if (! isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'group_id' => $groupId,
                    'department' => $department,
                    'subgroups' => [],
                    'total_entries' => 0,
                    'phone_count' => 0,
                    'email_count' => 0,
                    'sort_group' => $groupId ?? 999999,
                    'sort_sl' => $sl,
                ];
            }

            $subgroupKey = $subDepartment !== null ? 'sub:' . strtolower($subDepartment) : '__default__';

            if (! isset($groups[$groupKey]['subgroups'][$subgroupKey])) {
                $groups[$groupKey]['subgroups'][$subgroupKey] = [
                    'name' => $subDepartment,
                    'entries' => [],
                ];
            }

            $groups[$groupKey]['subgroups'][$subgroupKey]['entries'][] = [
                'sl' => $sl,
                'phone' => $phone,
                'phone_href' => $phone !== null ? static::phoneHref($phone) : null,
                'email' => $email,
                'email_href' => $email !== null ? 'mailto:' . preg_replace('/\s+/', '', $email) : null,
            ];

            $groups[$groupKey]['total_entries']++;
            $groups[$groupKey]['phone_count'] += $phone !== null ? 1 : 0;
            $groups[$groupKey]['email_count'] += $email !== null ? 1 : 0;
            $groups[$groupKey]['sort_sl'] = min($groups[$groupKey]['sort_sl'], $sl);
        }

        uasort($groups, function (array $left, array $right): int {
            return [$left['sort_group'], $left['sort_sl'], $left['department']]
                <=> [$right['sort_group'], $right['sort_sl'], $right['department']];
        });

        return array_map(function (array $group): array {
            $group['subgroups'] = array_values(array_map(function (array $subgroup): array {
                usort($subgroup['entries'], fn (array $left, array $right): int => $left['sl'] <=> $right['sl']);

                return $subgroup;
            }, $group['subgroups']));

            unset($group['sort_group'], $group['sort_sl']);

            return $group;
        }, array_values($groups));
    }

    public static function clearCaches(): void
    {
        $cache = PortalCache::store();

        foreach ([self::HIERARCHY_CACHE_KEY, self::PUBLIC_CACHE_KEY, self::PUBLIC_STALE_CACHE_KEY] as $key) {
            $cache->forget($key);
        }
    }

    public static function clean(mixed $value): string
    {
        return trim((string) $value);
    }

    private static function nullable(string $value): ?string
    {
        return $value !== '' ? $value : null;
    }

    private static function parseNumeric(?string $value): ?int
    {
        if ($value === null || preg_match('/^\d+$/', $value) !== 1) {
            return null;
        }

        return (int) $value;
    }

    private static function phoneHref(string $phone): string
    {
        $digits = preg_replace('/[^0-9+]/', '', $phone) ?: $phone;

        return 'tel:' . $digits;
    }
}
