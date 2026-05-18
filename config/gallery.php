<?php

use App\Support\PortalImageDirectory;

return [
    'albums_directory' => env(
        'GALLERY_ALBUMS_DIRECTORY',
        public_path(PortalImageDirectory::relativeDirectory(PortalImageDirectory::GALLERY_DIRECTORY))
    ),

    'relative_albums_directory' => env(
        'GALLERY_RELATIVE_ALBUMS_DIRECTORY',
        PortalImageDirectory::relativeDirectory(PortalImageDirectory::GALLERY_DIRECTORY)
    ),

    'optimize_images' => env('GALLERY_OPTIMIZE_IMAGES', true),
    'max_width' => (int) env('GALLERY_IMAGE_MAX_WIDTH', 1600),
    'max_height' => (int) env('GALLERY_IMAGE_MAX_HEIGHT', 1200),
    'jpeg_quality' => (int) env('GALLERY_IMAGE_JPEG_QUALITY', 78),
    'webp_quality' => (int) env('GALLERY_IMAGE_WEBP_QUALITY', 78),
];
