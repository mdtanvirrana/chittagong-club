<?php

namespace App\Http\Controllers;

use App\Support\PortalCache;
use App\Support\PortalImageDirectory;
use Illuminate\Support\Facades\File;

class GalleryController extends Controller
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function index()
    {
        PortalImageDirectory::ensurePhotoDirectories();

        $albums = collect(PortalCache::remember('gallery_albums_v1', now()->addMinutes(30), function (): array {
            $files = $this->galleryFiles();

            if ($files->isEmpty()) {
                return [];
            }

            $photos = $files
                ->map(fn ($file) => $this->versionedAssetUrl(
                    PortalImageDirectory::relativePath(
                        PortalImageDirectory::GALLERY_DIRECTORY,
                        $file->getFilename()
                    )
                ))
                ->values()
                ->all();

            return [[
                'id' => 'gallery',
                'title' => 'Gallery',
                'date' => date('M Y', $files->max(fn ($file) => $file->getMTime())),
                'cover' => $photos[0],
                'photos' => $photos,
            ]];
        }));

        return view('pages.gallery', compact('albums'));
    }

    private function galleryFiles()
    {
        $directory = PortalImageDirectory::absoluteDirectory(PortalImageDirectory::GALLERY_DIRECTORY);

        if (! is_dir($directory)) {
            return collect();
        }

        return collect(File::files($directory))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), self::IMAGE_EXTENSIONS, true))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values();
    }

    private function versionedAssetUrl(string $relativePath): string
    {
        $path = public_path($relativePath);
        $version = max((int) @filemtime($path), (int) @filectime($path));

        return asset($relativePath) . ($version > 0 ? '?v=' . $version : '');
    }
}
