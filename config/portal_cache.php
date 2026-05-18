<?php

return [
    'version' => env('PORTAL_CACHE_VERSION', 'v1'),

    'ttl' => [
        'global' => (int) env('PORTAL_CACHE_GLOBAL_TTL', 1800),
        'user' => (int) env('PORTAL_CACHE_USER_TTL', 300),
        'stale' => (int) env('PORTAL_CACHE_STALE_TTL', 86400),
        'photo_index' => (int) env('PORTAL_CACHE_PHOTO_INDEX_TTL', 1800),
    ],

    'http' => [
        'public_max_age' => (int) env('PORTAL_HTTP_PUBLIC_MAX_AGE', 300),
        'private_max_age' => (int) env('PORTAL_HTTP_PRIVATE_MAX_AGE', 60),
        'stale_while_revalidate' => (int) env('PORTAL_HTTP_STALE_WHILE_REVALIDATE', 300),
    ],
];
