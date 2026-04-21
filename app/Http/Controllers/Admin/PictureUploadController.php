<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\PortalCache;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PictureUploadController extends Controller
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    private const PER_PAGE = 48;

    public function index(): View
    {
        $directory = public_path('images');
        File::ensureDirectoryExists($directory);

        $files = collect(File::files($directory))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), self::IMAGE_EXTENSIONS, true))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pageItems = $files
            ->forPage($currentPage, self::PER_PAGE)
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'url' => asset('images/'.$file->getFilename()),
                'size_kb' => number_format($file->getSize() / 1024, 1),
                'updated_at' => date('M d, Y g:i A', $file->getMTime()),
            ])
            ->values();

        $pictures = new LengthAwarePaginator(
            $pageItems,
            $files->count(),
            self::PER_PAGE,
            $currentPage,
            [
                'path' => route('admin.pictures.index'),
                'pageName' => 'page',
            ]
        );

        return view('admin.pictures.index', compact('pictures'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'mimes:'.implode(',', self::IMAGE_EXTENSIONS), 'max:10240'],
        ]);

        $directory = public_path('images');
        File::ensureDirectoryExists($directory);

        $storedNames = [];

        foreach ($validated['images'] as $image) {
            if (! $image instanceof UploadedFile || ! $image->isValid()) {
                continue;
            }

            $filename = $this->resolveFilename($image);
            $targetPath = $directory.DIRECTORY_SEPARATOR.$filename;

            if (is_file($targetPath)) {
                File::delete($targetPath);
            }

            $image->move($directory, $filename);
            $storedNames[] = $filename;
        }

        PortalCache::clearPhotoRelatedCaches();

        return redirect()
            ->route('admin.pictures.index')
            ->with('status', count($storedNames).' picture(s) uploaded to public/images: '.implode(', ', $storedNames));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'filename' => ['required', 'string'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $requestedFilename = trim((string) $validated['filename']);
        $filename = basename($requestedFilename);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (
            $filename === ''
            || $filename !== $requestedFilename
            || ! in_array($extension, self::IMAGE_EXTENSIONS, true)
        ) {
            return redirect()
                ->route('admin.pictures.index', ['page' => $validated['page'] ?? 1])
                ->with('status', 'Invalid image filename.');
        }

        $targetPath = public_path('images'.DIRECTORY_SEPARATOR.$filename);

        if (! is_file($targetPath)) {
            return redirect()
                ->route('admin.pictures.index', ['page' => $validated['page'] ?? 1])
                ->with('status', $filename.' was not found in public/images.');
        }

        File::delete($targetPath);
        PortalCache::clearPhotoRelatedCaches();

        return redirect()
            ->route('admin.pictures.index', ['page' => $validated['page'] ?? 1])
            ->with('status', $filename.' deleted from public/images.');
    }

    private function resolveFilename(UploadedFile $image): string
    {
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = strtolower($image->getClientOriginalExtension() ?: $image->extension() ?: 'jpg');
        $basename = Str::slug($originalName, '-');

        if ($basename === '') {
            $basename = 'image-'.now()->format('YmdHis');
        }

        return $basename.'.'.$extension;
    }
}
