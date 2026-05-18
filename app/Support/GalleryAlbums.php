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

    public static function albums(bool $includePhotos = true): Collection
    {
        static::ensureBaseDirectory();

        return collect(File::directories(static::baseDirectory()))
            ->map(fn (string $directory) => static::albumPayload($directory, $includePhotos))
            ->filter()
            ->sortByDesc(fn (array $album) => $album['latest_timestamp'])
            ->values()
            ->map(function (array $album) {
                unset($album['latest_timestamp']);

                return $album;
            });
    }

    public static function albumsForAdmin(): Collection
    {
        static::ensureBaseDirectory();

        return collect(File::directories(static::baseDirectory()))
            ->map(fn (string $directory) => static::adminAlbumPayload($directory))
            ->filter()
            ->sortByDesc(fn (array $album) => $album['latest_timestamp'])
            ->values()
            ->map(function (array $album) {
                unset($album['latest_timestamp']);

                return $album;
            });
    }

    public static function albumSummary(string $slug): ?array
    {
        $slug = static::normalizeSlug($slug);

        if ($slug === null) {
            return null;
        }

        $directory = static::albumDirectory($slug);

        if (! is_dir($directory)) {
            return null;
        }

        $album = static::albumPayload($directory, false);

        if ($album === null) {
            return null;
        }

        unset($album['latest_timestamp']);

        return $album;
    }

    public static function albumForAdmin(string $slug): ?array
    {
        $slug = static::normalizeSlug($slug);

        if ($slug === null) {
            return null;
        }

        $directory = static::albumDirectory($slug);

        if (! is_dir($directory)) {
            return null;
        }

        $album = static::adminAlbumPayload($directory);

        if ($album === null) {
            return null;
        }

        unset($album['latest_timestamp']);

        return $album;
    }

    public static function albumPhotos(string $slug): array
    {
        $slug = static::normalizeSlug($slug);

        if ($slug === null) {
            return [];
        }

        $directory = static::albumDirectory($slug);

        if (! is_dir($directory)) {
            return [];
        }

        return static::imageFiles($directory)
            ->map(fn ($file) => static::versionedAssetUrl(static::relativePath($slug, $file->getFilename())))
            ->values()
            ->all();
    }

    public static function updateTitle(string $slug, string $title): ?array
    {
        $slug = static::normalizeSlug($slug);
        $title = trim($title);

        if ($slug === null || $title === '') {
            return null;
        }

        $directory = static::albumDirectory($slug);

        if (! is_dir($directory)) {
            return null;
        }

        static::writeMetadata($directory, $slug, $title);

        return static::albumForAdmin($slug);
    }

    public static function deleteAlbum(string $slug): bool
    {
        $slug = static::normalizeSlug($slug);

        if ($slug === null) {
            return false;
        }

        $directory = static::albumDirectory($slug);

        return is_dir($directory) && File::deleteDirectory($directory);
    }

    public static function renamePhoto(string $slug, string $filename, string $newName): ?string
    {
        return static::updatePhoto($slug, $filename, $newName);
    }

    public static function updatePhoto(
        string $slug,
        string $filename,
        string $newName,
        ?UploadedFile $replacement = null
    ): ?string
    {
        $slug = static::normalizeSlug($slug);
        $filename = static::normalizeFilename($filename);

        if ($slug === null || $filename === null) {
            return null;
        }

        $directory = static::albumDirectory($slug);
        $sourcePath = $directory.DIRECTORY_SEPARATOR.$filename;

        if (! is_file($sourcePath)) {
            return null;
        }

        $nameWithoutExtension = trim(pathinfo($newName, PATHINFO_FILENAME));
        $nameWithoutExtension = $nameWithoutExtension !== '' ? $nameWithoutExtension : trim($newName);
        $basename = Str::slug($nameWithoutExtension, '-');

        if ($basename === '') {
            return null;
        }

        $extension = $replacement instanceof UploadedFile && $replacement->isValid()
            ? strtolower($replacement->getClientOriginalExtension() ?: $replacement->extension() ?: pathinfo($filename, PATHINFO_EXTENSION))
            : strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (! in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            return null;
        }

        $newFilename = static::uniqueFilename($directory, $basename, $extension, $filename);
        $targetPath = $directory.DIRECTORY_SEPARATOR.$newFilename;

        if ($replacement instanceof UploadedFile && $replacement->isValid()) {
            $temporaryFilename = '.tmp-gallery-'.Str::random(16).'.'.$extension;
            GalleryImageOptimizer::store($replacement, $directory, $temporaryFilename);

            if (is_file($sourcePath)) {
                File::delete($sourcePath);
            }

            File::move($directory.DIRECTORY_SEPARATOR.$temporaryFilename, $targetPath);
        } elseif ($newFilename !== $filename) {
            File::move($sourcePath, $targetPath);
        }

        static::touchMetadata($directory, $slug);

        return $newFilename;
    }

    public static function deletePhoto(string $slug, string $filename): bool
    {
        $slug = static::normalizeSlug($slug);
        $filename = static::normalizeFilename($filename);

        if ($slug === null || $filename === null) {
            return false;
        }

        $directory = static::albumDirectory($slug);
        $targetPath = $directory.DIRECTORY_SEPARATOR.$filename;

        if (! is_file($targetPath)) {
            return false;
        }

        File::delete($targetPath);
        static::touchMetadata($directory, $slug);

        return true;
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
            GalleryImageOptimizer::store($image, $directory, $filename);
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

    private static function albumPayload(string $directory, bool $includePhotos = true, bool $includeEmpty = false): ?array
    {
        $slug = basename($directory);
        $photos = static::imageFiles($directory);

        if ($photos->isEmpty() && ! $includeEmpty) {
            return null;
        }

        $metadata = static::metadata($directory, $slug);
        $metadataTimestamp = static::metadataTimestamp($directory);
        $latestPhotoTimestamp = $photos->isNotEmpty()
            ? (int) $photos->max(fn ($file) => $file->getMTime())
            : 0;
        $latestTimestamp = max($latestPhotoTimestamp, $metadataTimestamp, (int) @filemtime($directory));
        $cover = $photos->isNotEmpty()
            ? static::versionedAssetUrl(static::relativePath($slug, $photos->first()->getFilename()))
            : null;
        $fileSignature = $photos
            ->map(fn ($file) => $file->getFilename().':'.$file->getSize().':'.$file->getMTime())
            ->implode('|');
        $album = [
            'id' => $slug,
            'title' => $metadata['title'],
            'date' => $latestPhotoTimestamp > 0 ? date('M Y', $latestPhotoTimestamp) : 'No photos',
            'cover' => $cover,
            'photo_count' => $photos->count(),
            'cache_key' => sha1($slug.'|'.$metadata['title'].'|'.$metadataTimestamp.'|'.$fileSignature),
            'latest_timestamp' => $latestTimestamp,
        ];

        if ($includePhotos) {
            $album['photos'] = $photos
                ->map(fn ($file) => static::versionedAssetUrl(static::relativePath($slug, $file->getFilename())))
                ->values()
                ->all();
        }

        return $album;
    }

    private static function adminAlbumPayload(string $directory): ?array
    {
        $slug = basename($directory);
        $album = static::albumPayload($directory, false, true);

        if ($album === null) {
            return null;
        }

        $album['photos'] = static::imageFiles($directory)
            ->map(fn ($file) => static::photoPayload($slug, $file))
            ->values()
            ->all();

        return $album;
    }

    private static function photoPayload(string $slug, $file): array
    {
        $relativePath = static::relativePath($slug, $file->getFilename());

        return [
            'filename' => $file->getFilename(),
            'name' => pathinfo($file->getFilename(), PATHINFO_FILENAME),
            'relative_path' => $relativePath,
            'url' => static::versionedAssetUrl($relativePath),
            'size_kb' => number_format($file->getSize() / 1024, 1),
            'updated_at' => date('M d, Y g:i A', $file->getMTime()),
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

    private static function metadataTimestamp(string $directory): int
    {
        $path = $directory.DIRECTORY_SEPARATOR.self::METADATA_FILENAME;

        return is_file($path) ? (int) @filemtime($path) : 0;
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

    private static function normalizeSlug(string $slug): ?string
    {
        $slug = trim($slug);

        if ($slug === '' || $slug !== basename($slug)) {
            return null;
        }

        return preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]*$/', $slug) === 1 ? $slug : null;
    }

    private static function normalizeFilename(string $filename): ?string
    {
        $filename = trim(str_replace('\\', '/', $filename));
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (
            $filename === ''
            || $filename !== basename($filename)
            || ! in_array($extension, self::IMAGE_EXTENSIONS, true)
        ) {
            return null;
        }

        return $filename;
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

    private static function uniqueFilename(
        string $directory,
        string $basename,
        string $extension,
        ?string $currentFilename = null
    ): string {
        $filename = $basename.'.'.$extension;
        $counter = 2;

        while (is_file($directory.DIRECTORY_SEPARATOR.$filename) && $filename !== $currentFilename) {
            $filename = $basename.'-'.$counter.'.'.$extension;
            $counter++;
        }

        return $filename;
    }

    private static function touchMetadata(string $directory, string $slug): void
    {
        $metadata = static::metadata($directory, $slug);

        static::writeMetadata($directory, $slug, $metadata['title']);
    }
}
