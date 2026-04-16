<?php

namespace App\Http\Controllers;

use App\Support\PortalCache;
use Illuminate\Support\Facades\DB;

class ClubFacilitiesController extends Controller
{
    public function index()
    {
        $fallbackImage = asset('images/placeholder/placeholder.jpeg');

        $facilities = collect(PortalCache::remember(
            'club_facilities_v1',
            now()->addMinutes(30),
            function () use ($fallbackImage): array {
                return DB::table('List_Department')
                    ->select('Departmentid', 'Departmentname')
                    ->whereNotNull('Departmentname')
                    ->whereRaw("LTRIM(RTRIM(Departmentname)) <> ''")
                    ->orderBy('Departmentname')
                    ->get()
                    ->map(function ($department) use ($fallbackImage) {
                        $imagePath = $this->resolveDepartmentImagePath((string) $department->Departmentid);

                        return [
                            'id' => (string) $department->Departmentid,
                            'name' => trim((string) $department->Departmentname),
                            'image_url' => $imagePath ? asset($imagePath) : $fallbackImage,
                        ];
                    })
                    ->values()
                    ->all();
            }
        ));

        return view('pages.club-facilities', compact('facilities'));
    }

    private function resolveDepartmentImagePath(string $departmentId): ?string
    {
        $basePath = 'images/departments/' . $departmentId;
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
}
