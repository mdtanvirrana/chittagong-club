<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\GalleryAlbums;
use App\Support\PortalCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryAlbumController extends Controller
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function index(Request $request): View
    {
        $albums = GalleryAlbums::albumsForAdmin();
        $selectedAlbumId = trim((string) $request->query('album', ''));

        if ($selectedAlbumId === '' && $albums->isNotEmpty()) {
            $selectedAlbumId = (string) $albums->first()['id'];
        }

        if (! $albums->contains(fn (array $album): bool => $album['id'] === $selectedAlbumId)) {
            $selectedAlbumId = null;
        }

        $selectedAlbum = $selectedAlbumId
            ? GalleryAlbums::albumForAdmin($selectedAlbumId)
            : null;

        return view('admin.gallery.index', compact('albums', 'selectedAlbumId', 'selectedAlbum'));
    }

    public function create(): View
    {
        $albums = GalleryAlbums::albumsForAdmin();

        return view('admin.gallery.create', compact('albums'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'album_name' => ['required', 'string', 'max:120'],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'mimes:'.implode(',', self::IMAGE_EXTENSIONS), 'max:10240'],
        ]);

        $result = GalleryAlbums::store($validated['album_name'], $validated['images']);
        PortalCache::clearPhotoRelatedCaches();

        return redirect()
            ->route('admin.gallery.create')
            ->with(
                'status',
                count($result['stored_names']).' picture(s) uploaded to "'.$result['title'].'" album.'
            );
    }

    public function update(Request $request, string $album): RedirectResponse
    {
        $validated = $request->validate([
            'album_name' => ['required', 'string', 'max:120'],
        ]);

        $updatedAlbum = GalleryAlbums::updateTitle($album, $validated['album_name']);
        PortalCache::clearPhotoRelatedCaches();

        if (! $updatedAlbum) {
            return redirect()
                ->route('admin.gallery.index')
                ->with('status', 'Gallery album was not found.');
        }

        return redirect()
            ->route('admin.gallery.index', ['album' => $updatedAlbum['id']])
            ->with('status', 'Gallery album updated.');
    }

    public function destroy(string $album): RedirectResponse
    {
        $deleted = GalleryAlbums::deleteAlbum($album);
        PortalCache::clearPhotoRelatedCaches();

        return redirect()
            ->route('admin.gallery.index')
            ->with('status', $deleted ? 'Gallery album deleted.' : 'Gallery album was not found.');
    }

    public function updateImage(Request $request, string $album, string $image): RedirectResponse
    {
        $validated = $request->validate([
            'image_name' => ['required', 'string', 'max:120'],
            'image_file' => ['nullable', 'image', 'mimes:'.implode(',', self::IMAGE_EXTENSIONS), 'max:10240'],
        ]);

        $updated = GalleryAlbums::updatePhoto($album, $image, $validated['image_name'], $request->file('image_file'));
        PortalCache::clearPhotoRelatedCaches();

        return redirect()
            ->route('admin.gallery.index', ['album' => $album])
            ->with('status', $updated ? 'Gallery image updated.' : 'Gallery image was not found.');
    }

    public function destroyImage(string $album, string $image): RedirectResponse
    {
        $deleted = GalleryAlbums::deletePhoto($album, $image);
        PortalCache::clearPhotoRelatedCaches();

        return redirect()
            ->route('admin.gallery.index', ['album' => $album])
            ->with('status', $deleted ? 'Gallery image deleted.' : 'Gallery image was not found.');
    }
}
