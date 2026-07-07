<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class ImageVariants
{
    private const OPTIMIZABLE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
    private const CACHE_DIRECTORY = 'cache/image-variants';

    public static function memberThumbUrl(mixed $path): ?string
    {
        return static::urlForPath($path, 160, 200, 64);
    }

    public static function memberPreviewUrl(mixed $path): ?string
    {
        return static::urlForPath($path, 720, 900, 74);
    }

    public static function galleryThumbUrl(mixed $path): ?string
    {
        return static::urlForPath($path, 360, 360, 68, 'cover');
    }

    public static function galleryPreviewUrl(mixed $path): ?string
    {
        return static::urlForPath($path, 1080, 1080, 74);
    }

    public static function galleryVariants(): array
    {
        return [
            [360, 360, 68, 'cover'],
            [1080, 1080, 74, 'contain'],
        ];
    }

    public static function memberVariants(): array
    {
        return [
            [160, 200, 64, 'contain'],
            [720, 900, 74, 'contain'],
        ];
    }

    public static function urlForPath(mixed $path, int $width = 320, int $height = 320, int $quality = 72, string $mode = 'contain'): ?string
    {
        $relativePath = static::relativePublicPath($path);

        if ($relativePath === null) {
            return null;
        }

        return static::url($relativePath, $width, $height, $quality, $mode);
    }

    public static function url(string $relativePath, int $width = 320, int $height = 320, int $quality = 72, string $mode = 'contain'): ?string
    {
        $relativePath = ltrim($relativePath, '/');
        $sourcePath = public_path($relativePath);

        if (! is_file($sourcePath) || ! static::isOptimizable($sourcePath)) {
            return null;
        }

        $version = max((int) @filemtime($sourcePath), (int) @filectime($sourcePath));
        $width = max(1, $width);
        $height = max(1, $height);
        $quality = min(100, max(1, $quality));
        $mode = $mode === 'cover' ? 'cover' : 'contain';
        $pathHash = sha1($relativePath);
        $versionHash = sha1($relativePath.'|'.$version);
        $cacheRelativePath = self::CACHE_DIRECTORY.'/'.$width.'x'.$height.'/'.$quality.'/'.$mode.'/'.substr($pathHash, 0, 2).'/'.$pathHash.'-'.$versionHash.'.webp';
        $cachePath = public_path($cacheRelativePath);

        if (! is_file($cachePath)) {
            try {
                File::ensureDirectoryExists(dirname($cachePath));

                $manager = new ImageManager(Driver::class, autoOrientation: true, decodeAnimation: false, strip: true);
                $image = $manager->decodePath($sourcePath);

                if ($mode === 'cover') {
                    $image = $image->coverDown($width, $height);
                } else {
                    $image = $image->scaleDown(width: $width, height: $height);
                }

                $image = $image->encode(new WebpEncoder(quality: $quality, strip: true));

                $image->save($cachePath);
            } catch (\Throwable) {
                if (is_file($cachePath)) {
                    File::delete($cachePath);
                }

                return null;
            }
        }

        return asset($cacheRelativePath).($version > 0 ? '?v='.$version : '');
    }

    public static function warm(string $relativePath, array $variants): int
    {
        $warmed = 0;

        foreach ($variants as $variant) {
            [$width, $height, $quality, $mode] = $variant + [320, 320, 72, 'contain'];

            if (static::url($relativePath, (int) $width, (int) $height, (int) $quality, (string) $mode)) {
                $warmed++;
            }
        }

        return $warmed;
    }

    public static function pruneForPath(mixed $path): int
    {
        $relativePath = static::relativePublicPath($path);

        if ($relativePath === null) {
            $value = trim((string) $path);
            $relativePath = $value !== '' ? ltrim(str_replace('\\', '/', $value), '/') : null;
        }

        if ($relativePath === null) {
            return 0;
        }

        $hash = sha1($relativePath);
        $deleted = 0;
        $baseDirectory = public_path(self::CACHE_DIRECTORY);

        if (! is_dir($baseDirectory)) {
            return 0;
        }

        foreach (File::allFiles($baseDirectory) as $file) {
            if (str_starts_with($file->getFilename(), $hash.'-')) {
                File::delete($file->getPathname());
                $deleted++;
            }
        }

        return $deleted;
    }

    private static function relativePublicPath(mixed $path): ?string
    {
        $value = PortalContent::cleanedOptionalField($path);

        if (! $value) {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $urlPath = parse_url($value, PHP_URL_PATH);

            if (! is_string($urlPath) || $urlPath === '') {
                return null;
            }

            $relativePath = ltrim($urlPath, '/');

            return is_file(public_path($relativePath)) ? $relativePath : null;
        }

        $relativePath = ltrim($value, '/');

        return is_file(public_path($relativePath)) ? $relativePath : null;
    }

    private static function isOptimizable(string $path): bool
    {
        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::OPTIMIZABLE_EXTENSIONS, true);
    }
}
