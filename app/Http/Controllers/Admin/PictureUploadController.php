<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\PortalImageDirectory;
use App\Support\PortalCache;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PictureUploadController extends Controller
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    private const PER_PAGE = 48;

    public function create(): View
    {
        PortalImageDirectory::ensurePhotoDirectories();

        $uploadTargets = PortalImageDirectory::uploadTargets();
        $departments = $this->departmentOptions();

        return view('admin.pictures.create', compact('uploadTargets', 'departments'));
    }

    public function index(Request $request): View
    {
        PortalImageDirectory::ensurePhotoDirectories();

        $photoFolders = PortalImageDirectory::photoFolders();
        $selectedFolder = trim((string) $request->query('folder', ''));
        $selectedFolder = in_array($selectedFolder, $photoFolders, true) ? $selectedFolder : null;
        $search = trim((string) $request->query('q', ''));

        $folderSummaries = collect($photoFolders)
            ->map(function (string $folder) {
                $files = $this->filesForFolder($folder);

                return [
                    'folder' => $folder,
                    'folder_label' => PortalImageDirectory::labelForFolder($folder),
                    'count' => $files->count(),
                    'latest_update' => $files->isNotEmpty()
                        ? date('M d, Y g:i A', $files->max(fn (array $item) => $item['file']->getMTime()))
                        : null,
                ];
            })
            ->values();

        $files = $selectedFolder
            ? $this->filesForFolder($selectedFolder, $search)
            : collect();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pageItems = $files
            ->forPage($currentPage, self::PER_PAGE)
            ->map(fn (array $item) => $this->picturePayload($item))
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

        $pictures->appends(array_filter([
            'folder' => $selectedFolder,
            'q' => $search !== '' ? $search : null,
        ]));

        return view('admin.pictures.index', compact(
            'pictures',
            'folderSummaries',
            'selectedFolder',
            'search'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'image_type' => ['required', 'string', 'in:'.implode(',', PortalImageDirectory::uploadTargetKeys())],
            'department_id' => [
                'nullable',
                'string',
                'required_if:image_type,facilities_photo',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || trim((string) $value) === '') {
                        return;
                    }

                    $exists = DB::table('List_Department')
                        ->where('Departmentid', trim((string) $value))
                        ->exists();

                    if (! $exists) {
                        $fail('Select a valid department.');
                    }
                },
            ],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'mimes:'.implode(',', self::IMAGE_EXTENSIONS), 'max:10240'],
        ]);

        $folder = PortalImageDirectory::folderForTarget($validated['image_type']);

        abort_if($folder === null, 422, 'Invalid upload target.');

        $directory = PortalImageDirectory::absoluteDirectory($folder);

        if ($folder === PortalImageDirectory::FACILITIES_DIRECTORY) {
            $directory .= DIRECTORY_SEPARATOR.trim((string) $validated['department_id']);
        }

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
            ->route('admin.pictures.index', ['folder' => $folder])
            ->with(
                'status',
                count($storedNames).' picture(s) uploaded to public/'.$this->storedRelativeDirectory($folder, $validated['department_id'] ?? null).': '.implode(', ', $storedNames)
            );
    }

    public function destroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'relative_path' => ['required', 'string'],
            'page' => ['nullable', 'integer', 'min:1'],
            'folder' => ['nullable', 'string'],
            'q' => ['nullable', 'string'],
        ]);

        $redirectParams = array_filter([
            'folder' => in_array((string) ($validated['folder'] ?? ''), PortalImageDirectory::photoFolders(), true)
                ? (string) $validated['folder']
                : null,
            'q' => filled($validated['q'] ?? null) ? trim((string) $validated['q']) : null,
            'page' => $validated['page'] ?? 1,
        ]);

        $requestedRelativePath = trim((string) $validated['relative_path']);
        $relativePath = str_replace('\\', '/', ltrim($requestedRelativePath, '/'));
        $filename = basename($relativePath);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (
            $relativePath === ''
            || $relativePath !== $requestedRelativePath
            || ! PortalImageDirectory::isManagedRelativePath($relativePath)
            || ! in_array($extension, self::IMAGE_EXTENSIONS, true)
        ) {
            return redirect()
                ->route('admin.pictures.index', $redirectParams)
                ->with('status', 'Invalid image path.');
        }

        $targetPath = public_path($relativePath);

        if (! is_file($targetPath)) {
            return redirect()
                ->route('admin.pictures.index', $redirectParams)
                ->with('status', $relativePath.' was not found in public/.');
        }

        File::delete($targetPath);
        PortalCache::clearPhotoRelatedCaches();

        return redirect()
            ->route('admin.pictures.index', $redirectParams)
            ->with('status', $relativePath.' deleted from public/.');
    }

    private function filesForFolder(string $folder, string $search = ''): Collection
    {
        $directory = PortalImageDirectory::absoluteDirectory($folder);

        $files = $folder === PortalImageDirectory::FACILITIES_DIRECTORY
            ? File::allFiles($directory)
            : File::files($directory);

        return collect($files)
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), self::IMAGE_EXTENSIONS, true))
            ->filter(function ($file) use ($search) {
                if ($search === '') {
                    return true;
                }

                return str_contains(
                    Str::lower($file->getFilename()),
                    Str::lower($search)
                );
            })
            ->map(fn ($file) => [
                'file' => $file,
                'folder' => $folder,
                'relative_path' => $this->relativePublicPath($file->getPathname()),
            ])
            ->sortByDesc(fn (array $item) => $item['file']->getMTime())
            ->values();
    }

    private function picturePayload(array $item): array
    {
        $file = $item['file'];
        $folder = $item['folder'];
        $relativePath = $item['relative_path'] ?? PortalImageDirectory::relativePath($folder, $file->getFilename());

        return [
            'name' => $file->getFilename(),
            'folder' => $folder,
            'folder_label' => PortalImageDirectory::labelForFolder($folder),
            'relative_path' => $relativePath,
            'url' => asset($relativePath),
            'size_kb' => number_format($file->getSize() / 1024, 1),
            'updated_at' => date('M d, Y g:i A', $file->getMTime()),
        ];
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

    private function departmentOptions(): array
    {
        return DB::table('List_Department')
            ->select('Departmentid', 'Departmentname')
            ->whereNotNull('Departmentname')
            ->whereRaw("LTRIM(RTRIM(Departmentname)) <> ''")
            ->orderBy('Departmentname')
            ->get()
            ->map(fn (object $department): array => [
                'id' => trim((string) $department->Departmentid),
                'name' => trim((string) $department->Departmentname),
            ])
            ->values()
            ->all();
    }

    private function storedRelativeDirectory(string $folder, mixed $departmentId = null): string
    {
        $relativeDirectory = PortalImageDirectory::relativeDirectory($folder);

        if ($folder === PortalImageDirectory::FACILITIES_DIRECTORY && filled($departmentId)) {
            return $relativeDirectory.'/'.trim((string) $departmentId);
        }

        return $relativeDirectory;
    }

    private function relativePublicPath(string $path): string
    {
        $publicPath = str_replace('\\', '/', public_path());
        $path = str_replace('\\', '/', $path);

        return ltrim(Str::after($path, $publicPath), '/');
    }
}
