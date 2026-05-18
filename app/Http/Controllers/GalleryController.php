<?php

namespace App\Http\Controllers;

use App\Support\GalleryAlbums;
use App\Support\PortalCache;
use Illuminate\Http\JsonResponse;

class GalleryController extends Controller
{
    public function index()
    {
        $albums = collect(PortalCache::remember('gallery_albums_v3', now()->addMinutes(30), function (): array {
            return GalleryAlbums::albums(false)->all();
        }))->map(fn (array $album): array => $album + [
            'photos_url' => route('gallery.photos', ['album' => $album['id']]),
        ]);

        return view('pages.gallery', compact('albums'));
    }

    public function photos(string $album): JsonResponse
    {
        $summary = GalleryAlbums::albumSummary($album);

        if (! $summary) {
            return response()->json(['message' => 'Album not found.'], 404);
        }

        $photos = PortalCache::remember(
            'gallery_album_photos_'.$summary['id'].'_'.$summary['cache_key'].'_v1',
            now()->addHours(6),
            fn (): array => GalleryAlbums::albumPhotos($summary['id'])
        );

        return response()
            ->json([
                'album' => $summary['id'],
                'cache_key' => $summary['cache_key'],
                'photos' => $photos,
            ])
            ->header('Cache-Control', PortalCache::cacheControlHeader('private', 300));
    }
}
