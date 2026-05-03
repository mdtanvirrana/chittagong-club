<?php

namespace App\Http\Controllers;

use App\Support\PortalCache;
use App\Support\PortalImageDirectory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ClubFacilitiesController extends Controller
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function index()
    {
        $fallbackImage = asset('images/placeholder/placeholder.jpeg');

        $facilities = collect(PortalCache::remember(
            'club_facilities_v2',
            now()->addMinutes(30),
            function () use ($fallbackImage): array {
                PortalImageDirectory::ensurePhotoDirectories();

                $uploadedImages = $this->uploadedFacilityImages();

                return DB::table('List_Department')
                    ->select('Departmentid', 'Departmentname')
                    ->whereNotNull('Departmentname')
                    ->whereRaw("LTRIM(RTRIM(Departmentname)) <> ''")
                    ->orderBy('Departmentname')
                    ->get()
                    ->values()
                    ->map(function ($department, int $index) use ($fallbackImage, $uploadedImages) {
                        $imageUrl = $this->resolveDepartmentImageUrl(
                            (string) $department->Departmentid,
                            (string) $department->Departmentname,
                            $uploadedImages,
                            $index
                        );

                        return [
                            'id' => (string) $department->Departmentid,
                            'name' => trim((string) $department->Departmentname),
                            'image_url' => $imageUrl ?: $fallbackImage,
                        ];
                    })
                    ->values()
                    ->all();
            }
        ));

        return view('pages.club-facilities', compact('facilities'));
    }

    private function resolveDepartmentImageUrl(
        string $departmentId,
        string $departmentName,
        array $uploadedImages,
        int $index
    ): ?string
    {
        foreach ([$departmentId, Str::slug($departmentName)] as $identifier) {
            $path = $this->resolveImagePath(PortalImageDirectory::FACILITIES_DIRECTORY, $identifier);

            if ($path) {
                return $this->versionedAssetUrl($path);
            }
        }

        $legacyPath = $this->resolveLegacyDepartmentImagePath($departmentId);

        if ($legacyPath) {
            return $this->versionedAssetUrl($legacyPath);
        }

        if ($uploadedImages !== []) {
            return $uploadedImages[$index % count($uploadedImages)]['url'];
        }

        return null;
    }

    private function resolveImagePath(string $folder, string $identifier): ?string
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return null;
        }

        $basePath = PortalImageDirectory::relativePath($folder, $identifier);
        $extensions = ['jpg', 'jpeg', 'png', 'webp'];

        foreach ($extensions as $extension) {
            $relativePath = $basePath . '.' . $extension;

            if (file_exists(public_path($relativePath))) {
                return $relativePath;
            }
        }

        if (file_exists(public_path($basePath))) {
            return $basePath;
        }

        return null;
    }

    private function resolveLegacyDepartmentImagePath(string $departmentId): ?string
    {
        $basePath = 'images/departments/' . trim($departmentId);
        $extensions = ['jpg', 'jpeg', 'png', 'webp'];

        foreach ($extensions as $extension) {
            $relativePath = $basePath . '.' . $extension;

            if (file_exists(public_path($relativePath))) {
                return $relativePath;
            }
        }

        if (file_exists(public_path($basePath))) {
            return $basePath;
        }

        return null;
    }

    private function uploadedFacilityImages(): array
    {
        $directory = PortalImageDirectory::absoluteDirectory(PortalImageDirectory::FACILITIES_DIRECTORY);

        if (! is_dir($directory)) {
            return [];
        }

        return collect(File::files($directory))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), self::IMAGE_EXTENSIONS, true))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(fn ($file) => [
                'url' => $this->versionedAssetUrl(
                    PortalImageDirectory::relativePath(
                        PortalImageDirectory::FACILITIES_DIRECTORY,
                        $file->getFilename()
                    )
                ),
            ])
            ->values()
            ->all();
    }

    private function versionedAssetUrl(string $relativePath): string
    {
        $path = public_path($relativePath);
        $version = max((int) @filemtime($path), (int) @filectime($path));

        return asset($relativePath) . ($version > 0 ? '?v=' . $version : '');
    }
}
