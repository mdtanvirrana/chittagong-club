<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ClubFacilities
{
    private const CACHE_KEY = 'club_facilities_v3';

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public static function all(): array
    {
        return PortalCache::remember(self::CACHE_KEY, now()->addMinutes(30), function (): array {
            PortalImageDirectory::ensurePhotoDirectories();

            $imageIndex = static::facilityImageIndex();

            return DB::table('List_Department')
                ->select('Departmentid', 'Departmentname')
                ->whereNotNull('Departmentname')
                ->whereRaw("LTRIM(RTRIM(Departmentname)) <> ''")
                ->orderBy('Departmentname')
                ->get()
                ->values()
                ->map(function (object $department) use ($imageIndex): array {
                    $departmentId = trim((string) $department->Departmentid);
                    $departmentName = trim((string) $department->Departmentname);
                    $imageUrls = static::resolveDepartmentImageUrls($departmentId, $departmentName, $imageIndex);
                    $imageUrl = $imageUrls[0] ?? null;

                    return [
                        'id' => $departmentId,
                        'name' => $departmentName,
                        'image_url' => $imageUrl,
                        'images' => $imageUrls,
                        'has_image' => $imageUrl !== null,
                    ];
                })
                ->values()
                ->all();
        });
    }

    private static function facilityImageIndex(): array
    {
        $directory = PortalImageDirectory::absoluteDirectory(PortalImageDirectory::FACILITIES_DIRECTORY);

        if (! is_dir($directory)) {
            return [];
        }

        $index = [];

        collect(File::files($directory))
            ->filter(fn ($file): bool => in_array(strtolower($file->getExtension()), self::IMAGE_EXTENSIONS, true))
            ->sortByDesc(fn ($file): int => $file->getMTime())
            ->each(function ($file) use (&$index): void {
                $basename = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $relativePath = PortalImageDirectory::relativePath(
                    PortalImageDirectory::FACILITIES_DIRECTORY,
                    $file->getFilename()
                );

                foreach (static::identifierKeys($basename) as $key) {
                    $index[$key] ??= [$relativePath];
                }
            });

        collect(File::directories($directory))
            ->each(function (string $departmentDirectory) use (&$index): void {
                $folder = basename($departmentDirectory);
                $paths = collect(File::files($departmentDirectory))
                    ->filter(fn ($file): bool => in_array(strtolower($file->getExtension()), self::IMAGE_EXTENSIONS, true))
                    ->sortByDesc(fn ($file): int => $file->getMTime())
                    ->map(fn ($file): string => PortalImageDirectory::relativePath(
                        PortalImageDirectory::FACILITIES_DIRECTORY,
                        $folder.'/'.$file->getFilename()
                    ))
                    ->values()
                    ->all();

                if ($paths === []) {
                    return;
                }

                foreach (static::identifierKeys($folder) as $key) {
                    $index[$key] = $paths;
                }
            });

        return $index;
    }

    private static function resolveDepartmentImageUrls(string $departmentId, string $departmentName, array $imageIndex): array
    {
        foreach (array_merge(static::identifierKeys($departmentId), static::identifierKeys($departmentName)) as $key) {
            $relativePaths = $imageIndex[$key] ?? [];

            if ($relativePaths !== []) {
                return array_map(
                    fn (string $relativePath): string => static::versionedAssetUrl($relativePath),
                    $relativePaths
                );
            }
        }

        return [];
    }

    private static function identifierKeys(string $identifier): array
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return [];
        }

        return array_values(array_unique(array_filter([
            Str::lower($identifier),
            Str::slug($identifier),
        ])));
    }

    private static function versionedAssetUrl(string $relativePath): string
    {
        $path = public_path($relativePath);
        $version = max((int) @filemtime($path), (int) @filectime($path));

        return asset($relativePath) . ($version > 0 ? '?v=' . $version : '');
    }
}
