<?php

namespace App\Support;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PortalCache
{
    private const PUBLIC_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public static function store(): Repository
    {
        return Cache::store('file');
    }

    public static function remember(string $key, mixed $ttl, callable $callback): mixed
    {
        return static::store()->remember($key, $ttl, $callback);
    }

    public static function rememberResilient(
        string $freshKey,
        string $staleKey,
        mixed $freshTtl,
        mixed $staleTtl,
        callable $callback,
        mixed $default = null
    ): mixed {
        $cache = static::store();

        if ($cache->has($freshKey)) {
            return $cache->get($freshKey);
        }

        try {
            $value = $callback();
            $cache->put($freshKey, $value, $freshTtl);
            $cache->put($staleKey, $value, $staleTtl);

            return $value;
        } catch (\Throwable) {
            if ($cache->has($staleKey)) {
                return $cache->get($staleKey);
            }

            return $default;
        }
    }

    public static function memberPhotoIndex(): array
    {
        return static::photoIndex(
            'public_image_index_member_directory_v2',
            [PortalImageDirectory::MEMBER_DIRECTORY, null]
        );
    }

    public static function employeePhotoIndex(): array
    {
        return static::photoIndex(
            'public_image_index_employee_directory_v2',
            [PortalImageDirectory::EMPLOYEE_DIRECTORY, null]
        );
    }

    public static function hasMemberPhoto(string|int|null $memberId): bool
    {
        return static::photoExists(static::memberPhotoIndex(), $memberId);
    }

    public static function memberPhotoUrl(string|int|null $memberId): ?string
    {
        return static::photoUrlFromIndex(static::memberPhotoIndex(), $memberId);
    }

    public static function hasEmployeePhoto(string|int|null $employeeId): bool
    {
        return static::photoExists(static::employeePhotoIndex(), $employeeId);
    }

    public static function employeePhotoUrl(string|int|null $employeeId): ?string
    {
        return static::photoUrlFromIndex(static::employeePhotoIndex(), $employeeId);
    }

    public static function clearPhotoRelatedCaches(): void
    {
        $cache = static::store();
        $currentYear = (int) now()->format('Y');
        $previousYear = $currentYear - 1;

        foreach ([
            'public_image_index_v1',
            'public_image_index_member_directory_v2',
            'public_image_index_employee_directory_v2',
            'member_directory_v4',
            'employee_directory_v2',
            'former_chairman_v2',
            'club_facilities_v1',
            'club_facilities_v2',
            'gallery_albums_v1',
            'gallery_albums_v2',
            "committee_members_{$currentYear}_{$previousYear}_v2",
        ] as $key) {
            $cache->forget($key);
        }
    }

    public static function clearMemberRelatedCaches(string|int|null $memberId): void
    {
        $memberId = trim((string) $memberId);

        if ($memberId === '') {
            return;
        }

        $cache = static::store();

        foreach ([
            "dashboard_member_{$memberId}_v2",
            "dashboard_member_{$memberId}_stale_v2",
            "dashboard_ledger_totals_{$memberId}_v2",
            "dashboard_ledger_totals_{$memberId}_stale_v2",
            "dashboard_member_credit_{$memberId}_v1",
            "dashboard_member_credit_{$memberId}_stale_v1",
            "member_profile_view_{$memberId}_v1",
            "member_profile_view_{$memberId}_v2",
        ] as $key) {
            $cache->forget($key);
        }
    }

    public static function clearAffiliatedClubCaches(): void
    {
        $cache = static::store();

        foreach ([
            'affiliated_clubs_v1',
            'affiliated_clubs_v2',
            'affiliated_clubs_v3',
            'affiliated_clubs_v4',
            'affiliated_clubs_v5',
        ] as $key) {
            $cache->forget($key);
        }
    }

    private static function photoIndex(string $cacheKey, array $folders): array
    {
        return static::remember($cacheKey, now()->addMinutes(30), function () use ($folders): array {
            $index = [];

            foreach ($folders as $folder) {
                $directory = $folder === null
                    ? public_path(PortalImageDirectory::BASE_DIRECTORY)
                    : PortalImageDirectory::absoluteDirectory($folder);

                if (! is_dir($directory)) {
                    continue;
                }

                collect(File::files($directory))
                    ->filter(
                        fn ($file) => in_array(
                            strtolower($file->getExtension()),
                            self::PUBLIC_IMAGE_EXTENSIONS,
                            true
                        )
                    )
                    ->sortByDesc(fn ($file) => $file->getMTime())
                    ->each(function ($file) use (&$index, $folder): void {
                        $basename = static::normalizeImageIdentifier(pathinfo($file->getFilename(), PATHINFO_FILENAME));

                        if ($basename !== '' && ! isset($index[$basename])) {
                            $index[$basename] = [
                                'filename' => $file->getFilename(),
                                'folder' => $folder,
                            ];
                        }
                    });
            }

            return $index;
        });
    }

    private static function photoExists(array $index, string|int|null $identifier): bool
    {
        $identifier = static::normalizeImageIdentifier($identifier);

        if ($identifier === '') {
            return false;
        }

        return isset($index[$identifier]);
    }

    private static function photoUrlFromIndex(array $index, string|int|null $identifier): ?string
    {
        $identifier = static::normalizeImageIdentifier($identifier);

        if ($identifier === '') {
            return null;
        }

        $entry = $index[$identifier] ?? null;

        if (! is_array($entry) || empty($entry['filename'])) {
            return null;
        }

        $folder = $entry['folder'] ?? null;
        $filename = $entry['filename'];
        $relativePath = $folder === null
            ? PortalImageDirectory::BASE_DIRECTORY.'/'.$filename
            : PortalImageDirectory::relativePath($folder, $filename);
        $path = public_path($relativePath);

        if (! is_file($path)) {
            return null;
        }

        $mtime = @filemtime($path) ?: 0;
        $ctime = @filectime($path) ?: 0;
        $version = max($mtime, $ctime);

        return asset($relativePath) . '?v=' . $version;
    }

    private static function normalizeImageIdentifier(string|int|null $identifier): string
    {
        return Str::lower(trim((string) $identifier));
    }
}
