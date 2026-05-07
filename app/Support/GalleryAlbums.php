<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GalleryAlbums
{
    public const METADATA_FILENAME = '_album.json';

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public static function baseDirectory(): string
    {
        return (string) config(
            'gallery.albums_directory',
            PortalImageDirectory::absoluteDirectory(PortalImageDirectory::GALLERY_DIRECTORY)
        );
    }

    public static function relativeBaseDirectory(): string
    {
        return trim((string) config(
            'gallery.relative_albums_directory',
            PortalImageDirectory::relativeDirectory(PortalImageDirectory::GALLERY_DIRECTORY)
        ), '/');
    }

    public static function ensureBaseDirectory(): void
    {
        File::ensureDirectoryExists(static::baseDirectory());
    }

    public static function albums(): Collection
    {
        static::ensureBaseDirectory();

        return collect(File::directories(static::baseDirectory()))
            ->map(fn (string $directory) => static::albumPayload($directory))
            ->filter()
            ->sortByDesc(fn (array $album) => $album['latest_timestamp'])
            ->values()
            ->map(function (array $album) {
                unset($album['latest_timestamp']);

                return $album;
            });
    }

    public static function store(string $title, array $images): array
    {
        $title = trim($title);
        $slug = static::slugForTitle($title);
        $directory = static::albumDirectory($slug);

        File::ensureDirectoryExists($directory);
        static::writeMetadata($directory, $slug, $title);

        $storedNames = [];

        foreach ($images as $image) {
            if (! $image instanceof UploadedFile || ! $image->isValid()) {
                continue;
            }

            $filename = static::resolveFilename($directory, $image);
            $image->move($directory, $filename);
            $storedNames[] = $filename;
        }

        static::writeMetadata($directory, $slug, $title);

        return [
            'slug' => $slug,
            'title' => $title,
            'stored_names' => $storedNames,
        ];
    }

    public static function slugForTitle(string $title): string
    {
        $slug = Str::slug($title, '-');

        return $slug !== '' ? $slug : 'album-'.substr(sha1($title), 0, 12);
    }

    private static function albumDirectory(string $slug): string
    {
        return static::baseDirectory().DIRECTORY_SEPARATOR.$slug;
    }

    private static function albumPayload(string $directory): ?array
    {
        $slug = basename($directory);
        $photos = static::imageFiles($directory);

        if ($photos->isEmpty()) {
            return null;
        }

        $metadata = static::metadata($directory, $slug);
        $photoUrls = $photos
            ->map(fn ($file) => static::versionedAssetUrl(static::relativePath($slug, $file->getFilename())))
            ->values()
            ->all();

        return [
            'id' => $slug,
            'title' => $metadata['title'],
            'date' => date('M Y', $photos->max(fn ($file) => $file->getMTime())),
            'cover' => $photoUrls[0],
            'photos' => $photoUrls,
            'photo_count' => count($photoUrls),
            'latest_timestamp' => $photos->max(fn ($file) => $file->getMTime()),
        ];
    }

    private static function imageFiles(string $directory): Collection
    {
        if (! is_dir($directory)) {
            return collect();
        }

        return collect(File::files($directory))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), self::IMAGE_EXTENSIONS, true))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values();
    }

    private static function metadata(string $directory, string $slug): array
    {
        $path = $directory.DIRECTORY_SEPARATOR.self::METADATA_FILENAME;
        $metadata = [];

        if (is_file($path)) {
            $decoded = json_decode((string) File::get($path), true);
            $metadata = is_array($decoded) ? $decoded : [];
        }

        return [
            'slug' => (string) ($metadata['slug'] ?? $slug),
            'title' => filled($metadata['title'] ?? null)
                ? trim((string) $metadata['title'])
                : Str::headline(str_replace(['-', '_'], ' ', $slug)),
            'created_at' => $metadata['created_at'] ?? null,
            'updated_at' => $metadata['updated_at'] ?? null,
        ];
    }

    private static function writeMetadata(string $directory, string $slug, string $title): void
    {
        $existing = static::metadata($directory, $slug);
        $now = now()->toIso8601String();

        File::put(
            $directory.DIRECTORY_SEPARATOR.self::METADATA_FILENAME,
            json_encode([
                'slug' => $slug,
                'title' => $title,
                'created_at' => $existing['created_at'] ?? $now,
                'updated_at' => $now,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private static function relativePath(string $slug, string $filename): string
    {
        return static::relativeBaseDirectory().'/'.$slug.'/'.$filename;
    }

    private static function versionedAssetUrl(string $relativePath): string
    {
        $path = public_path($relativePath);
        $version = max((int) @filemtime($path), (int) @filectime($path));

        return asset($relativePath).($version > 0 ? '?v='.$version : '');
    }

    private static function resolveFilename(string $directory, UploadedFile $image): string
    {
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = strtolower($image->getClientOriginalExtension() ?: $image->extension() ?: 'jpg');
        $basename = Str::slug($originalName, '-');
        $basename = $basename !== '' ? $basename : 'image-'.now()->format('YmdHis');
        $filename = $basename.'.'.$extension;
        $counter = 2;

        while (is_file($directory.DIRECTORY_SEPARATOR.$filename)) {
            $filename = $basename.'-'.$counter.'.'.$extension;
            $counter++;
        }

        return $filename;
    }
}
