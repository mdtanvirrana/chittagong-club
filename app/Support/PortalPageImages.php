<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class PortalPageImages
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public static function urls(string $folder): array
    {
        $directory = PortalImageDirectory::absoluteDirectory($folder);

        if (! is_dir($directory)) {
            return [];
        }

        return collect(File::files($directory))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), self::IMAGE_EXTENSIONS, true))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(fn ($file) => static::versionedAssetUrl(
                PortalImageDirectory::relativePath($folder, $file->getFilename())
            ))
            ->values()
            ->all();
    }

    private static function versionedAssetUrl(string $relativePath): string
    {
        $path = public_path($relativePath);
        $version = max((int) @filemtime($path), (int) @filectime($path));

        return asset($relativePath) . ($version > 0 ? '?v=' . $version : '');
    }
}
