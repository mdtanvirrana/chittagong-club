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

    public function create(): View
    {
        $albums = GalleryAlbums::albums();

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
}
