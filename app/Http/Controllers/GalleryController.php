<?php

namespace App\Http\Controllers;

use App\Support\GalleryAlbums;
use App\Support\PortalCache;

class GalleryController extends Controller
{
    public function index()
    {
        $albums = collect(PortalCache::remember('gallery_albums_v2', now()->addMinutes(30), function (): array {
            return GalleryAlbums::albums()->all();
        }));

        return view('pages.gallery', compact('albums'));
    }
}
