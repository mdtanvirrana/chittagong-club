<?php

namespace App\Support;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

class PortalCache
{
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
        return static::remember('member_photo_index_v1', now()->addMinutes(30), function (): array {
            $imageDir = public_path('images');

            if (! is_dir($imageDir)) {
                return [];
            }

            $paths = glob($imageDir . '/*.jpg') ?: [];
            $index = [];

            foreach ($paths as $path) {
                $filename = pathinfo($path, PATHINFO_FILENAME);

                if ($filename !== '') {
                    $index[$filename] = true;
                }
            }

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
}
