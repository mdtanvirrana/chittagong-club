<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class GalleryImageOptimizer
{
    private const OPTIMIZABLE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public static function store(UploadedFile $image, string $directory, string $filename): void
    {
        File::ensureDirectoryExists($directory);

        if (! self::shouldOptimize($image, $filename)) {
            $image->move($directory, $filename);

            return;
        }

        $targetPath = $directory.DIRECTORY_SEPARATOR.$filename;
        $temporaryPath = $directory.DIRECTORY_SEPARATOR.'.tmp-optimized-'.bin2hex(random_bytes(8)).'.'.$image->extension();

        try {
            $sourcePath = (string) $image->getRealPath();
            $originalSize = (int) @filesize($sourcePath);
            $manager = new ImageManager(Driver::class, autoOrientation: true, decodeAnimation: false, strip: true);
            $optimized = $manager
                ->decodePath($sourcePath)
                ->scaleDown(
                    width: max(1, (int) config('gallery.max_width', 1600)),
                    height: max(1, (int) config('gallery.max_height', 1200))
                )
                ->encode(self::encoderFor($filename));

            $optimized->save($temporaryPath);

            if ($originalSize > 0 && is_file($temporaryPath) && filesize($temporaryPath) > $originalSize) {
                File::delete($temporaryPath);
                $image->move($directory, $filename);

                return;
            }

            File::move($temporaryPath, $targetPath);
        } catch (\Throwable) {
            if (is_file($temporaryPath)) {
                File::delete($temporaryPath);
            }

            $image->move($directory, $filename);
        }
    }

    public static function optimizePath(string $path): bool
    {
        if (! is_file($path) || ! self::isOptimizableExtension($path)) {
            return false;
        }

        $temporaryPath = pathinfo($path, PATHINFO_DIRNAME)
            .DIRECTORY_SEPARATOR.'.tmp-optimized-'.bin2hex(random_bytes(8)).'.'.pathinfo($path, PATHINFO_EXTENSION);

        try {
            $originalSize = (int) @filesize($path);
            $manager = new ImageManager(Driver::class, autoOrientation: true, decodeAnimation: false, strip: true);
            $optimized = $manager
                ->decodePath($path)
                ->scaleDown(
                    width: max(1, (int) config('gallery.max_width', 1600)),
                    height: max(1, (int) config('gallery.max_height', 1200))
                )
                ->encode(self::encoderFor($path));

            $optimized->save($temporaryPath);

            if ($originalSize > 0 && is_file($temporaryPath) && filesize($temporaryPath) >= $originalSize) {
                File::delete($temporaryPath);

                return false;
            }

            File::move($temporaryPath, $path);

            return true;
        } catch (\Throwable) {
            if (is_file($temporaryPath)) {
                File::delete($temporaryPath);
            }

            return false;
        }
    }

    private static function shouldOptimize(UploadedFile $image, string $filename): bool
    {
        return (bool) config('gallery.optimize_images', true)
            && $image->isValid()
            && self::isOptimizableExtension($filename);
    }

    private static function isOptimizableExtension(string $filename): bool
    {
        return in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), self::OPTIMIZABLE_EXTENSIONS, true);
    }

    private static function encoderFor(string $filename): JpegEncoder|PngEncoder|WebpEncoder
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => new JpegEncoder(
                quality: min(100, max(1, (int) config('gallery.jpeg_quality', 78))),
                progressive: true,
                strip: true
            ),
            'webp' => new WebpEncoder(
                quality: min(100, max(1, (int) config('gallery.webp_quality', 78))),
                strip: true
            ),
            default => new PngEncoder(interlaced: true, indexed: false),
        };
    }
}
