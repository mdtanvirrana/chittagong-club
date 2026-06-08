<?php

namespace App\Support;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PortalCache
{
    private const PUBLIC_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    private static array $contentVersions = [];
    private static array $photoIndexes = [];

    public static function store(): Repository
    {
        return Cache::store();
    }

    public static function remember(string $key, mixed $ttl, callable $callback): mixed
    {
        return static::store()->remember($key, $ttl, $callback);
    }

    public static function rememberGlobal(string $name, mixed $ttl, callable $callback, ?string $version = null): mixed
    {
        return static::remember(static::globalKey($name, $version), $ttl, $callback);
    }

    public static function rememberUser(
        string|int|null $memberId,
        string $name,
        mixed $ttl,
        callable $callback,
        ?string $version = null
    ): mixed {
        return static::remember(static::userKey($memberId, $name, $version), $ttl, $callback);
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

    public static function rememberGlobalResilient(
        string $name,
        mixed $freshTtl,
        mixed $staleTtl,
        callable $callback,
        mixed $default = null,
        ?string $version = null
    ): mixed {
        $freshKey = static::globalKey($name, $version);

        return static::rememberResilient(
            $freshKey,
            static::staleKey($freshKey),
            $freshTtl,
            $staleTtl,
            $callback,
            $default
        );
    }

    public static function rememberUserResilient(
        string|int|null $memberId,
        string $name,
        mixed $freshTtl,
        mixed $staleTtl,
        callable $callback,
        mixed $default = null,
        ?string $version = null
    ): mixed {
        $freshKey = static::userKey($memberId, $name, $version);

        return static::rememberResilient(
            $freshKey,
            static::staleKey($freshKey),
            $freshTtl,
            $staleTtl,
            $callback,
            $default
        );
    }

    public static function globalKey(string $name, ?string $version = null): string
    {
        return 'global:'.static::version($version).':'.static::normalizeKeySegment($name);
    }

    public static function userKey(string|int|null $memberId, string $name, ?string $version = null): string
    {
        return 'user:'.static::normalizeKeySegment($memberId).':'.static::version($version).':'.static::normalizeKeySegment($name);
    }

    public static function staleKey(string $key): string
    {
        return $key.':stale';
    }

    public static function ttl(string $type): int
    {
        return max(1, (int) config("portal_cache.ttl.{$type}", 300));
    }

    public static function contentVersion(string $scope): int
    {
        $scope = static::normalizeKeySegment($scope);

        if (isset(static::$contentVersions[$scope])) {
            return static::$contentVersions[$scope];
        }

        $value = (int) static::store()->get(static::contentVersionKey($scope), 1);

        return static::$contentVersions[$scope] = max(1, $value);
    }

    public static function bumpContentVersion(string $scope): int
    {
        $key = static::contentVersionKey($scope);
        $value = static::store()->increment($key);

        if (! is_int($value) || $value < 1) {
            $value = 2;
            static::store()->forever($key, $value);
        }

        static::$contentVersions[static::normalizeKeySegment($scope)] = $value;

        return $value;
    }

    public static function publicJson(array $payload, int $status = 200, ?int $maxAge = null): JsonResponse
    {
        return static::json($payload, $status, 'public', $maxAge);
    }

    public static function privateJson(array $payload, int $status = 200, ?int $maxAge = null): JsonResponse
    {
        return static::json($payload, $status, 'private', $maxAge);
    }

    public static function noStoreJson(array $payload, int $status = 200): JsonResponse
    {
        return response()
            ->json($payload, $status)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public static function cacheControlHeader(string $visibility = 'private', ?int $maxAge = null, ?int $stale = null): string
    {
        $visibility = $visibility === 'public' ? 'public' : 'private';
        $defaultMaxAge = $visibility === 'public'
            ? (int) config('portal_cache.http.public_max_age', 300)
            : (int) config('portal_cache.http.private_max_age', 60);
        $maxAge = max(0, $maxAge ?? $defaultMaxAge);
        $stale = max(0, $stale ?? (int) config('portal_cache.http.stale_while_revalidate', 300));

        return "{$visibility}, max-age={$maxAge}, stale-while-revalidate={$stale}";
    }

    private static function json(array $payload, int $status, string $visibility, ?int $maxAge = null): JsonResponse
    {
        return response()
            ->json($payload, $status)
            ->header('Cache-Control', static::cacheControlHeader($visibility, $maxAge))
            ->header('Vary', 'Accept, Authorization');
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

    public static function memberPhotoThumbUrl(string|int|null $memberId): ?string
    {
        return static::photoUrlFromIndex(static::memberPhotoIndex(), $memberId, 160, 200, 64)
            ?? static::memberPhotoUrl($memberId);
    }

    public static function memberPhotoPreviewUrl(string|int|null $memberId): ?string
    {
        return static::photoUrlFromIndex(static::memberPhotoIndex(), $memberId, 720, 900, 74)
            ?? static::memberPhotoUrl($memberId);
    }

    public static function hasEmployeePhoto(string|int|null $employeeId): bool
    {
        return static::photoExists(static::employeePhotoIndex(), $employeeId);
    }

    public static function employeePhotoUrl(string|int|null $employeeId): ?string
    {
        return static::photoUrlFromIndex(static::employeePhotoIndex(), $employeeId);
    }

    public static function employeePhotoThumbUrl(string|int|null $employeeId): ?string
    {
        return static::photoUrlFromIndex(static::employeePhotoIndex(), $employeeId, 160, 200, 64)
            ?? static::employeePhotoUrl($employeeId);
    }

    public static function employeePhotoPreviewUrl(string|int|null $employeeId): ?string
    {
        return static::photoUrlFromIndex(static::employeePhotoIndex(), $employeeId, 720, 900, 74)
            ?? static::employeePhotoUrl($employeeId);
    }

    public static function clearPhotoRelatedCaches(): void
    {
        $cache = static::store();
        $currentYear = (int) now()->format('Y');
        $previousYear = $currentYear - 1;
        $apiCommitteeKey = "api_committee_members_{$currentYear}_{$previousYear}_v1";

        foreach ([
            'public_image_index_v1',
            'public_image_index_member_directory_v2',
            'public_image_index_employee_directory_v2',
            'member_directory_v4',
            'api_member_directory_v1',
            'employee_directory_v2',
            'api_employee_directory_v1',
            'api_employee_directory_v2',
            'former_chairman_v2',
            'api_former_chairmen_v1',
            'club_facilities_v1',
            'club_facilities_v2',
            'club_facilities_v3',
            'api_club_facilities_v1',
            'gallery_albums_v1',
            'gallery_albums_v2',
            'gallery_albums_v3',
            'api_gallery_albums_v1',
            'api_gallery_albums_v2',
            "committee_members_{$currentYear}_{$previousYear}_v2",
            $apiCommitteeKey,
            static::globalKey('api_member_directory'),
            static::globalKey('api_member_directory_v2'),
        ] as $key) {
            $cache->forget($key);
        }

        foreach (['gallery', 'people-images', 'member-directory', 'employee-directory'] as $scope) {
            static::bumpContentVersion($scope);
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
            "member_ledger_data_{$memberId}_v1",
            "member_ledger_data_{$memberId}_stale_v1",
            "member_ledger_summary_{$memberId}_v1",
            "member_ledger_summary_{$memberId}_stale_v1",
            "member_ledger_insights_{$memberId}_v1",
            "member_ledger_insights_{$memberId}_stale_v1",
            "member_ledger_history_{$memberId}_v1",
            "member_ledger_history_{$memberId}_stale_v1",
            "member_ledger_payments_{$memberId}_v1",
            "member_ledger_payments_{$memberId}_stale_v1",
            "member_profile_view_{$memberId}_v1",
            "member_profile_view_{$memberId}_v2",
            static::userKey($memberId, 'api_dashboard_member'),
            static::staleKey(static::userKey($memberId, 'api_dashboard_member')),
            static::userKey($memberId, 'api_dashboard_ledger_totals'),
            static::staleKey(static::userKey($memberId, 'api_dashboard_ledger_totals')),
        ] as $key) {
            $cache->forget($key);
        }
    }

    public static function clearCompanyProfileCaches(): void
    {
        static::store()->forget(static::globalKey('app_config'));
    }

    public static function clearAffiliatedClubCaches(): void
    {
        $cache = static::store();
        $version = max(1, (int) $cache->get(static::contentVersionKey('affiliated-clubs'), 1));

        foreach ([
            'affiliated_clubs_v1',
            'affiliated_clubs_v2',
            'affiliated_clubs_v3',
            'affiliated_clubs_v4',
            'affiliated_clubs_v5',
            "affiliated_clubs_v5_{$version}",
            'api_affiliated_clubs_v1',
            "api_affiliated_clubs_v1_{$version}",
        ] as $key) {
            $cache->forget($key);
        }

        static::bumpContentVersion('affiliated-clubs');
    }

    private static function photoIndex(string $cacheKey, array $folders): array
    {
        if (isset(static::$photoIndexes[$cacheKey])) {
            return static::$photoIndexes[$cacheKey];
        }

        return static::$photoIndexes[$cacheKey] = static::remember($cacheKey, now()->addSeconds(static::ttl('photo_index')), function () use ($folders): array {
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

    private static function photoUrlFromIndex(array $index, string|int|null $identifier, ?int $width = null, ?int $height = null, int $quality = 72): ?string
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

        if ($width !== null && $height !== null) {
            return ImageVariants::url($relativePath, $width, $height, $quality);
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

    private static function contentVersionKey(string $scope): string
    {
        return 'content-version:'.static::normalizeKeySegment($scope);
    }

    private static function version(?string $version = null): string
    {
        return static::normalizeKeySegment($version ?: config('portal_cache.version', 'v1'));
    }

    private static function normalizeKeySegment(string|int|null $value): string
    {
        $value = Str::lower(trim((string) $value));

        return $value === '' ? 'unknown' : (string) preg_replace('/[^a-z0-9_.:-]+/', '-', $value);
    }
}
