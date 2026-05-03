<?php

$brandRed = '#FE0002';
$brandRedRgb = '254, 0, 2';
$brandRedStrong = '#C90002';
$brandRedStrongRgb = '201, 0, 2';
$brandRedDeep = '#930001';
$brandRedDeepRgb = '147, 0, 1';

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
            'primary' => $brandRed,
            'primary_glow' => "rgba({$brandRedRgb}, 0.28)",
            'secondary' => $brandRedStrong,
            'secondary_deep' => $brandRedDeep,
            'navy' => $brandRedDeep,
            'shell_start' => '#ffffff',
            'shell_end' => '#fff1f1',
            'shell_glow' => "rgba({$brandRedRgb}, 0.10)",
            'shell_start_overlay' => "rgba({$brandRedStrongRgb}, 0.86)",
            'accent' => $brandRed,
            'surface_base' => '#fff6f6',
            'surface' => 'rgba(255, 255, 255, 0.92)',
            'surface_soft' => 'rgba(255, 255, 255, 0.86)',
            'surface_border' => "rgba({$brandRedRgb}, 0.12)",
            'surface_border_soft' => "rgba({$brandRedRgb}, 0.08)",
            'text' => '#111827',
            'text_soft' => '#1f2937',
            'text_muted' => '#1f2937',
        ],
        'gradients' => [
            'shell' => "radial-gradient(circle at top, rgba({$brandRedRgb}, 0.10), transparent 28%), linear-gradient(180deg, #ffffff 0%, #fff1f1 100%)",
            'accent' => "linear-gradient(135deg, {$brandRed} 0%, {$brandRedStrong} 100%)",
            'login' => "radial-gradient(circle at top, rgba({$brandRedRgb}, 0.16), transparent 34%), linear-gradient(180deg, #ffffff 0%, #fff3f3 100%)",
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
            'text' => '#111827',
            'text_soft' => '#475569',
            'text_muted' => '#64748b',
            'panel' => $brandRedDeep,
            'panel_55' => 'rgba(255, 255, 255, 0.84)',
            'soft' => '#ffe8e8',
            'soft_20' => "rgba({$brandRedRgb}, 0.04)",
            'soft_40' => "rgba({$brandRedRgb}, 0.08)",
            'surface' => 'rgba(255, 255, 255, 0.94)',
            'surface_strong' => 'rgba(255, 255, 255, 0.98)',
            'line' => '#f2c9c9',
            'line_active' => '#eca1a1',
            'overlay' => $brandRedDeep,
            'overlay_95' => "rgba({$brandRedDeepRgb}, 0.95)",
            'mist' => '#475569',
            'accent' => $brandRed,
            'login_support' => '#f38f90',
            'backdrop_70' => 'rgba(15, 23, 42, 0.42)',
            'background_glow' => "rgba({$brandRedRgb}, 0.08)",
            'background_start' => '#fff6f6',
            'background_end' => '#ffffff',
            'login_glow_primary' => "rgba({$brandRedRgb}, 0.12)",
            'login_glow_accent' => "rgba({$brandRedStrongRgb}, 0.08)",
            'login_background_start' => '#fff4f4',
            'login_background_end' => '#ffffff',
        ],
        'shadow' => [
            'panel' => '0 18px 42px rgba(127, 29, 29, 0.16)',
        ],
    ],
];
