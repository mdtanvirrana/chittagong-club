<?php

return [
    // Member / user portal theme tokens.
    'member' => [
        'fonts' => [
            'display' => [
                'family' => 'Manrope',
                'weights' => '300;400;500;600;700;800',
            ],
        ],
        'radius' => [
            'default' => '0.5rem',
            'lg' => '1rem',
            'xl' => '1.5rem',
            'full' => '9999px',
        ],
        'colors' => [
            'primary' => '#c5162e',
            'primary_glow' => 'rgba(197, 22, 46, 0.28)',
            'secondary' => '#ba1731',
            'secondary_deep' => '#8f1025',
            'navy' => '#7a0f22',
            'shell_start' => '#ffffff',
            'shell_end' => '#fff3f4',
            'shell_glow' => 'rgba(197, 22, 46, 0.10)',
            'shell_start_overlay' => 'rgba(186, 23, 49, 0.86)',
            'accent' => '#d61f3e',
            'surface_base' => '#fff7f7',
            'surface' => 'rgba(255, 255, 255, 0.92)',
            'surface_soft' => 'rgba(255, 255, 255, 0.86)',
            'surface_border' => 'rgba(197, 22, 46, 0.12)',
            'surface_border_soft' => 'rgba(197, 22, 46, 0.08)',
            'text' => '#111827',
            'text_soft' => '#475569',
            'text_muted' => '#64748b',
        ],
        'gradients' => [
            'shell' => 'radial-gradient(circle at top, rgba(197, 22, 46, 0.10), transparent 28%), linear-gradient(180deg, #ffffff 0%, #fff3f4 100%)',
            'accent' => 'linear-gradient(135deg, #d61f3e 0%, #ad0f28 100%)',
            'login' => 'radial-gradient(circle at top, rgba(197, 22, 46, 0.16), transparent 34%), linear-gradient(180deg, #ffffff 0%, #fff4f5 100%)',
        ],
    ],

    // Admin panel theme tokens.
    'admin' => [
        'fonts' => [
            'display' => [
                'family' => 'Space Grotesk',
                'weights' => '500;700',
            ],
            'body' => [
                'family' => 'Instrument Sans',
                'weights' => '400;500;600;700',
            ],
        ],
        'colors' => [
            'ink' => '#ffffff',
            'panel' => '#a1122d',
            'panel_55' => 'rgba(255, 255, 255, 0.84)',
            'soft' => '#fde8ea',
            'soft_20' => 'rgba(197, 22, 46, 0.04)',
            'soft_40' => 'rgba(197, 22, 46, 0.08)',
            'line' => '#ecc5cc',
            'line_active' => '#d67a88',
            'overlay' => '#8f1025',
            'overlay_95' => 'rgba(143, 16, 37, 0.95)',
            'mist' => '#475569',
            'accent' => '#c5162e',
            'login_support' => '#ef8797',
            'backdrop_70' => 'rgba(15, 23, 42, 0.42)',
            'background_glow' => 'rgba(197, 22, 46, 0.08)',
            'background_start' => '#fff7f7',
            'background_end' => '#ffffff',
            'login_glow_primary' => 'rgba(197, 22, 46, 0.12)',
            'login_glow_accent' => 'rgba(239, 68, 68, 0.08)',
            'login_background_start' => '#fff5f6',
            'login_background_end' => '#ffffff',
        ],
        'shadow' => [
            'panel' => '0 18px 42px rgba(127, 29, 29, 0.16)',
        ],
    ],
];
