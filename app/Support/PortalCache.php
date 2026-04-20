<?php

namespace App\Support;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

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
        return static::remember('public_image_index_v1', now()->addMinutes(30), function (): array {
            $imageDir = public_path('images');

            if (! is_dir($imageDir)) {
                return [];
            }

            $index = [];
            collect(File::files($imageDir))
                ->filter(
                    fn ($file) => in_array(
                        strtolower($file->getExtension()),
                        self::PUBLIC_IMAGE_EXTENSIONS,
                        true
                    )
                )
                ->sortByDesc(fn ($file) => $file->getMTime())
                ->each(function ($file) use (&$index): void {
                    $basename = pathinfo($file->getFilename(), PATHINFO_FILENAME);

                    if ($basename !== '' && ! isset($index[$basename])) {
                        $index[$basename] = $file->getFilename();
                    }
                });

            return $index;
        });
    }

    public static function hasMemberPhoto(string|int|null $memberId): bool
    {
        if ($memberId === null || $memberId === '') {
            return false;
        }

        return isset(static::memberPhotoIndex()[(string) $memberId]);
    }

    public static function memberPhotoUrl(string|int|null $memberId): ?string
    {
        return static::publicImageUrl($memberId);
    }

    public static function hasEmployeePhoto(string|int|null $employeeId): bool
    {
        if ($employeeId === null || $employeeId === '') {
            return false;
        }

        return isset(static::memberPhotoIndex()[(string) $employeeId]);
    }

    public static function employeePhotoUrl(string|int|null $employeeId): ?string
    {
        return static::publicImageUrl($employeeId);
    }

    public static function clearPhotoRelatedCaches(): void
    {
        $cache = static::store();
        $currentYear = (int) now()->format('Y');
        $previousYear = $currentYear - 1;

        foreach ([
            'public_image_index_v1',
            'member_directory_v4',
            'employee_directory_v2',
            'former_chairman_v2',
            "committee_members_{$currentYear}_{$previousYear}_v2",
        ] as $key) {
            $cache->forget($key);
        }
    }

    private static function publicImageUrl(string|int|null $identifier): ?string
    {
        if ($identifier === null || $identifier === '') {
            return null;
        }

        $filename = static::memberPhotoIndex()[(string) $identifier] ?? null;

        if ($filename === null) {
            return null;
        }

        $path = public_path('images/' . $filename);

        if (! is_file($path)) {
            return null;
        }

        $mtime = @filemtime($path) ?: 0;
        $ctime = @filectime($path) ?: 0;
        $version = max($mtime, $ctime);

        return asset('images/' . $filename) . '?v=' . $version;
    }
}
