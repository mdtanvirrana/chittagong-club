<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@hasSection('page_title')@yield('page_title') — {{ $companyName }}@else{{ $companyName }}@endif</title>
    <link rel="icon" type="image/x-icon" href="{{ $companyFaviconUrl }}" />
    <link rel="apple-touch-icon" href="{{ $companyLogoUrl }}" />
    <link rel="preconnect" href="https://cdn.tailwindcss.com" crossorigin />
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="dns-prefetch" href="//cdn.tailwindcss.com" />
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net" />
    @php
        $memberTheme = config('theme.member', []);
        $memberDisplayFont = data_get($memberTheme, 'fonts.display.family', 'Manrope');
        $memberDisplayWeights = data_get($memberTheme, 'fonts.display.weights', '300;400;500;600;700;800');
        $memberDisplayFontUrl = str_replace(' ', '+', $memberDisplayFont) . ':wght@' . $memberDisplayWeights;
        $hexToRgb = static function ($hex, $fallback) {
            $hex = ltrim(trim((string) $hex), '#');

            if (strlen($hex) === 3) {
                $hex = preg_replace('/(.)/', '$1$1', $hex);
            }

            if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
                return $fallback;
            }

            return implode(', ', [
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2)),
            ]);
        };
        $memberPrimaryRgb = $hexToRgb(data_get($memberTheme, 'colors.primary', '#c5162e'), '197, 22, 46');
        $memberInkRgb = $hexToRgb(data_get($memberTheme, 'colors.text', '#000000'), '0, 0, 0');
        $memberInkSoftRgb = $hexToRgb(data_get($memberTheme, 'colors.text_soft', '#000000'), '0, 0, 0');
        $memberInkMutedRgb = $hexToRgb(data_get($memberTheme, 'colors.text_muted', '#000000'), '0, 0, 0');
        $memberContrast = data_get($memberTheme, 'colors.text_contrast', '#fff7f7');
        $memberContrastSoft = data_get($memberTheme, 'colors.text_contrast_soft', '#ffe2e2');
        $memberContrastMuted = data_get($memberTheme, 'colors.text_contrast_muted', '#ffc9c9');
        $memberContrastRgb = $hexToRgb($memberContrast, '255, 247, 247');
        $memberContrastSoftRgb = $hexToRgb($memberContrastSoft, '255, 226, 226');
        $memberContrastMutedRgb = $hexToRgb($memberContrastMuted, '255, 201, 201');
    @endphp

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    {{-- Alpine.js CDN --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family={{ $memberDisplayFontUrl }}&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary:           @js(data_get($memberTheme, 'colors.primary', '#c5162e')),
                        'brand-blue':      @js(data_get($memberTheme, 'colors.secondary', '#ba1731')),
                        'background-dark': @js(data_get($memberTheme, 'colors.surface_base', '#fff7f7')),
                        'club-navy':       @js(data_get($memberTheme, 'colors.secondary_deep', '#8f1025')),
                        'club-deep':       @js(data_get($memberTheme, 'colors.secondary_deep', '#8f1025')),
                        'brand-start':     @js(data_get($memberTheme, 'colors.shell_start', '#ffffff')),
                        'club-gold':       @js(data_get($memberTheme, 'colors.accent', '#d61f3e')),
                    },
                    fontFamily: {
                        display: [@js($memberDisplayFont), 'sans-serif'],
                    },
                    borderRadius: {
                        DEFAULT: @js(data_get($memberTheme, 'radius.default', '0.5rem')),
                        lg: @js(data_get($memberTheme, 'radius.lg', '1rem')),
                        xl: @js(data_get($memberTheme, 'radius.xl', '1.5rem')),
                        full: @js(data_get($memberTheme, 'radius.full', '9999px')),
                    },
                },
            },
        };
    </script>

    <style>
        :root {
            color-scheme: light;
            --member-primary: {{ data_get($memberTheme, 'colors.primary', '#c5162e') }};
            --member-primary-rgb: {{ $memberPrimaryRgb }};
            --member-primary-glow: {{ data_get($memberTheme, 'colors.primary_glow', 'rgba(197, 22, 46, 0.28)') }};
            --member-secondary: {{ data_get($memberTheme, 'colors.secondary', '#ba1731') }};
            --member-secondary-deep: {{ data_get($memberTheme, 'colors.secondary_deep', '#8f1025') }};
            --member-panel-navy: {{ data_get($memberTheme, 'colors.navy', '#7a0f22') }};
            --member-shell-start: {{ data_get($memberTheme, 'colors.shell_start', '#ffffff') }};
            --member-shell-end: {{ data_get($memberTheme, 'colors.shell_end', '#fff3f4') }};
            --member-shell-glow: {{ data_get($memberTheme, 'colors.shell_glow', 'rgba(197, 22, 46, 0.10)') }};
            --member-shell-start-overlay: {{ data_get($memberTheme, 'colors.shell_start_overlay', 'rgba(186, 23, 49, 0.86)') }};
            --member-surface-base: {{ data_get($memberTheme, 'colors.surface_base', '#fff7f7') }};
            --member-surface: {{ data_get($memberTheme, 'colors.surface', 'rgba(255, 255, 255, 0.92)') }};
            --member-surface-soft: {{ data_get($memberTheme, 'colors.surface_soft', 'rgba(255, 255, 255, 0.86)') }};
            --member-border: {{ data_get($memberTheme, 'colors.surface_border', 'rgba(197, 22, 46, 0.12)') }};
            --member-border-soft: {{ data_get($memberTheme, 'colors.surface_border_soft', 'rgba(197, 22, 46, 0.08)') }};
            --member-ink: {{ data_get($memberTheme, 'colors.text', '#000000') }};
            --member-ink-rgb: {{ $memberInkRgb }};
            --member-ink-soft: {{ data_get($memberTheme, 'colors.text_soft', '#000000') }};
            --member-ink-soft-rgb: {{ $memberInkSoftRgb }};
            --member-ink-muted: {{ data_get($memberTheme, 'colors.text_muted', '#000000') }};
            --member-ink-muted-rgb: {{ $memberInkMutedRgb }};
            --member-contrast: {{ $memberContrast }};
            --member-contrast-rgb: {{ $memberContrastRgb }};
            --member-contrast-soft: {{ $memberContrastSoft }};
            --member-contrast-soft-rgb: {{ $memberContrastSoftRgb }};
            --member-contrast-muted: {{ $memberContrastMuted }};
            --member-contrast-muted-rgb: {{ $memberContrastMutedRgb }};
            --member-shell-gradient: {{ data_get($memberTheme, 'gradients.shell', 'radial-gradient(circle at top, rgba(197, 22, 46, 0.10), transparent 28%), linear-gradient(180deg, #ffffff 0%, #fff3f4 100%)') }};
            --member-accent-gradient: {{ data_get($memberTheme, 'gradients.accent', 'linear-gradient(135deg, #d61f3e 0%, #ad0f28 100%)') }};
            --member-login-gradient: {{ data_get($memberTheme, 'gradients.login', 'radial-gradient(circle at top, rgba(197, 22, 46, 0.16), transparent 34%), linear-gradient(180deg, #ffffff 0%, #fff4f5 100%)') }};
        }

        .mobile-container {
            max-width: 425px;
            margin: 0 auto;
            background: #ffffff;
            min-height: 100dvh;
            position: relative;
            box-shadow: 0 36px 84px -56px rgba(127, 29, 29, 0.32);
        }

        body {
            font-family: '{{ $memberDisplayFont }}', sans-serif;
            -webkit-tap-highlight-color: transparent;
            min-height: 100dvh;
            background: #ffffff;
        }

        .member-shell {
            color: #000000;
            --userpanel-header-offset: 4.5rem;
        }

        [x-cloak] {
            display: none !important;
        }

        .member-shell .userpanel-subheader {
            top: var(--userpanel-header-offset) !important;
            z-index: 40 !important;
        }

        /* Keep app headers on the exact primary red without recoloring other brand-blue panels. */
        .member-shell header[class~="bg-brand-blue"] {
            background-color: var(--member-primary) !important;
        }

        .member-shell header[class~="bg-brand-blue/95"] {
            background-color: rgba(var(--member-primary-rgb), 0.95) !important;
        }

        .member-shell header[class~="bg-brand-blue/90"] {
            background-color: rgba(var(--member-primary-rgb), 0.90) !important;
        }

        .member-shell header[class~="bg-brand-blue/80"] {
            background-color: rgba(var(--member-primary-rgb), 0.80) !important;
        }

        .member-shell header[class~="bg-brand-blue/60"] {
            background-color: rgba(var(--member-primary-rgb), 0.60) !important;
        }

        .member-shell input::placeholder,
        .member-shell textarea::placeholder {
            color: var(--member-ink-muted) !important;
            opacity: 1;
        }

        .ios-blur {
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .member-modal-backdrop {
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        .gold-gradient,
        .gold-btn-gradient {
            background: var(--member-accent-gradient);
            box-shadow: 0 18px 32px -24px rgba(185, 28, 28, 0.45);
        }

        .gold-text-gradient {
            background: var(--member-accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .card-glass {
            background: var(--member-surface);
            backdrop-filter: blur(14px);
            border: 1px solid var(--member-border);
        }

        .member-shell .member-modal-surface {
            background: #ffffff !important;
            border-color: var(--member-border) !important;
            color: var(--member-ink);
            box-shadow: 0 32px 72px -44px rgba(var(--member-primary-rgb), 0.34);
        }

        .member-shell .member-modal-surface [class~="bg-white/10"] {
            background-color: rgba(var(--member-primary-rgb), 0.08) !important;
            box-shadow: none;
        }

        .member-shell .member-modal-surface [class~="bg-white/20"] {
            background-color: rgba(var(--member-ink-muted-rgb), 0.18) !important;
        }

        .member-shell .member-avatar-photo {
            display: block;
            height: 100%;
            width: 100%;
            box-sizing: border-box;
            object-fit: contain !important;
            object-position: center top !important;
            padding: 0.25rem;
            background: #ffffff;
        }

        .member-shell .member-avatar-photo-preview {
            padding: 0;
        }

        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .gold-border {
            border: 2px solid var(--member-primary);
            box-shadow: 0 14px 28px -24px rgba(185, 28, 28, 0.5);
        }

        .member-shell [class~="bg-background-dark/90"] {
            background-color: rgba(255, 255, 255, 0.96) !important;
            box-shadow: 0 -16px 36px -30px rgba(185, 28, 28, 0.26);
        }

        .member-shell [class~="bg-background-dark/80"],
        .member-shell [class~="bg-background-dark"] {
            background-color: var(--member-surface-base) !important;
        }

        .member-shell [class~="bg-white/10"] {
            background-color: var(--member-surface) !important;
            box-shadow: 0 18px 40px -32px rgba(185, 28, 28, 0.24);
        }

        .member-shell [class~="bg-white/5"] {
            background-color: var(--member-surface-soft) !important;
            box-shadow: 0 16px 36px -32px rgba(185, 28, 28, 0.18);
        }

        .member-shell [class~="bg-white/[0.04]"],
        .member-shell [class~="bg-white/[0.03]"],
        .member-shell [class~="bg-white/[0.02]"] {
            background-color: var(--member-surface) !important;
        }

        .member-shell [class~="bg-primary"] {
            background: var(--member-accent-gradient) !important;
        }

        .member-shell [class~="bg-primary/15"],
        .member-shell [class~="bg-primary/10"],
        .member-shell [class~="bg-primary/5"] {
            background-color: rgba(197, 22, 46, 0.08) !important;
        }

        .member-shell [class~="border-white/10"] {
            border-color: var(--member-border) !important;
        }

        .member-shell [class~="border-white/8"],
        .member-shell [class~="border-white/5"] {
            border-color: var(--member-border-soft) !important;
        }

        .member-shell [class~="border-primary/30"],
        .member-shell [class~="border-primary/25"],
        .member-shell [class~="border-primary/20"],
        .member-shell [class~="border-primary/10"] {
            border-color: var(--member-border) !important;
        }

        .member-shell [class~="divide-white/10"] > :not([hidden]) ~ :not([hidden]) {
            border-color: var(--member-border) !important;
        }

        .member-shell [class~="divide-white/5"] > :not([hidden]) ~ :not([hidden]) {
            border-color: var(--member-border-soft) !important;
        }

        .member-shell [class~="text-white"],
        .member-shell [class~="text-white/90"],
        .member-shell [class~="text-white/85"],
        .member-shell [class~="text-white/80"],
        .member-shell [class~="text-white/75"] {
            color: var(--member-ink) !important;
        }

        .member-shell [class~="text-white/70"],
        .member-shell [class~="text-white/65"],
        .member-shell [class~="text-white/60"] {
            color: var(--member-ink-soft) !important;
        }

        .member-shell [class~="text-white/55"],
        .member-shell [class~="text-white/50"],
        .member-shell [class~="text-white/45"],
        .member-shell [class~="text-white/40"],
        .member-shell [class~="text-white/35"],
        .member-shell [class~="text-white/30"],
        .member-shell [class~="text-white/25"],
        .member-shell [class~="text-white/20"],
        .member-shell [class~="text-white/15"],
        .member-shell [class~="text-white/10"] {
            color: var(--member-ink-muted) !important;
        }

        .member-shell [class~="text-slate-900"],
        .member-shell [class~="text-slate-800"],
        .member-shell [class~="text-slate-700"],
        .member-shell [class~="text-gray-900"],
        .member-shell [class~="text-gray-800"],
        .member-shell [class~="text-gray-700"],
        .member-shell [class~="text-zinc-900"],
        .member-shell [class~="text-zinc-800"],
        .member-shell [class~="text-zinc-700"],
        .member-shell [class~="text-neutral-900"],
        .member-shell [class~="text-neutral-800"],
        .member-shell [class~="text-neutral-700"] {
            color: #000000 !important;
        }

        .member-shell [class~="text-slate-600"],
        .member-shell [class~="text-slate-500"],
        .member-shell [class~="text-slate-400"],
        .member-shell [class~="text-slate-300"],
        .member-shell [class~="text-gray-600"],
        .member-shell [class~="text-gray-500"],
        .member-shell [class~="text-gray-400"],
        .member-shell [class~="text-gray-300"],
        .member-shell [class~="text-zinc-600"],
        .member-shell [class~="text-zinc-500"],
        .member-shell [class~="text-zinc-400"],
        .member-shell [class~="text-zinc-300"],
        .member-shell [class~="text-neutral-600"],
        .member-shell [class~="text-neutral-500"],
        .member-shell [class~="text-neutral-400"],
        .member-shell [class~="text-neutral-300"] {
            color: #000000 !important;
        }
        .member-shell [class~="text-club-gold"] { color: var(--member-primary) !important; }
        .member-shell [class~="text-brand-blue"] { color: var(--member-contrast) !important; }

        .member-shell [class~="bg-[#071e33]"],
        .member-shell [class~="bg-[#0a3d62]"] {
            background: linear-gradient(180deg, var(--member-secondary) 0%, var(--member-secondary-deep) 100%) !important;
        }

        .member-shell .member-sidebar {
            background: #ffffff !important;
            border-color: var(--member-border) !important;
            box-shadow: 0 24px 48px -34px rgba(185, 28, 28, 0.28);
        }

        .member-shell .member-sidebar * {
            color: inherit;
        }

        .member-shell .member-sidebar [class~="bg-white"] {
            background-color: #ffffff !important;
        }

        .member-shell .member-sidebar [class~="bg-white/10"],
        .member-shell .member-sidebar [class~="bg-white/15"] {
            background-color: rgba(197, 22, 46, 0.08) !important;
            box-shadow: none;
        }

        .member-shell .member-sidebar [class~="border-white/15"],
        .member-shell .member-sidebar [class~="border-white/10"] {
            border-color: var(--member-border) !important;
        }

        .member-shell .member-sidebar [class~="text-primary"] {
            color: var(--member-primary) !important;
        }

        .member-shell .member-sidebar [class~="text-white"] {
            color: var(--member-ink) !important;
        }

        .member-shell .member-sidebar [class~="text-white/90"],
        .member-shell .member-sidebar [class~="text-white/85"] {
            color: var(--member-primary) !important;
        }

        .member-shell .member-sidebar [class~="text-white/80"] {
            color: rgba(var(--member-primary-rgb), 0.78) !important;
        }

        .member-shell .member-sidebar [class~="text-white/60"],
        .member-shell .member-sidebar [class~="text-white/50"],
        .member-shell .member-sidebar [class~="text-white/40"] {
            color: var(--member-ink-muted) !important;
        }

        .member-shell [class~="from-[#02568a]/90"] {
            --tw-gradient-from: var(--member-shell-start-overlay) var(--tw-gradient-from-position) !important;
        }

        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="bg-white/10"] {
            background-color: rgba(255, 255, 255, 0.12) !important;
            box-shadow: none;
        }

        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="bg-white/5"] {
            background-color: rgba(255, 255, 255, 0.08) !important;
            box-shadow: none;
        }

        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="bg-primary/15"],
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="bg-primary/10"],
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="bg-primary/5"] {
            background-color: rgba(255, 255, 255, 0.14) !important;
        }

        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="border-white/10"] {
            border-color: rgba(255, 255, 255, 0.16) !important;
        }

        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="border-primary/30"],
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="border-primary/25"],
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="border-primary/20"] {
            border-color: rgba(255, 255, 255, 0.16) !important;
        }

        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="divide-white/10"] > :not([hidden]) ~ :not([hidden]) {
            border-color: rgba(255, 255, 255, 0.14) !important;
        }

        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white"] { color: var(--member-contrast) !important; }
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/90"],
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/85"],
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/80"] { color: rgba(var(--member-contrast-rgb), 0.82) !important; }
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/75"] { color: rgba(var(--member-contrast-rgb), 0.76) !important; }
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/70"],
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/65"],
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/60"] { color: rgba(var(--member-contrast-soft-rgb), 0.88) !important; }
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/55"] { color: rgba(var(--member-contrast-soft-rgb), 0.84) !important; }
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/50"] { color: rgba(var(--member-contrast-soft-rgb), 0.78) !important; }
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/45"] { color: rgba(var(--member-contrast-soft-rgb), 0.72) !important; }
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/40"] { color: rgba(var(--member-contrast-soft-rgb), 0.64) !important; }
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/35"],
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/30"] { color: rgba(var(--member-contrast-muted-rgb), 0.70) !important; }
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/25"] { color: rgba(var(--member-contrast-muted-rgb), 0.62) !important; }
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/20"] { color: rgba(var(--member-contrast-muted-rgb), 0.52) !important; }
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/15"],
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/10"] { color: rgba(var(--member-contrast-muted-rgb), 0.46) !important; }
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-primary"] { color: var(--member-contrast-soft) !important; }
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-brand-blue"] { color: var(--member-contrast) !important; }

        .member-shell [class~="bg-primary"][class~="text-brand-blue"],
        .member-shell [class~="bg-primary"] [class~="text-brand-blue"] {
            color: var(--member-contrast) !important;
        }

        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) input::placeholder,
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) textarea::placeholder {
            color: rgba(var(--member-contrast-soft-rgb), 0.68) !important;
        }

        .member-notify-stack {
            position: fixed;
            left: 1rem;
            right: 1rem;
            top: max(1rem, env(safe-area-inset-top));
            z-index: 1000;
            display: grid;
            gap: 0.75rem;
            pointer-events: none;
        }

        .member-notify-toast {
            pointer-events: auto;
            overflow: hidden;
            border-radius: 1rem;
            border: 1px solid rgba(var(--member-primary-rgb), 0.18);
            background: #ffffff;
            box-shadow: 0 22px 54px -34px rgba(var(--member-primary-rgb), 0.55);
            color: #000000;
        }

        .member-notify-toast button,
        .member-notify-toast a {
            touch-action: manipulation;
        }

        .member-route-progress {
            position: fixed;
            top: 0;
            left: 50%;
            z-index: 1200;
            height: 3px;
            width: min(100vw, 425px);
            max-width: 425px;
            background: var(--member-primary);
            opacity: 0;
            transform: translateX(-50%) scaleX(0);
            transform-origin: left top;
            transition: transform 220ms ease, opacity 160ms ease;
        }

        body.ccl-route-loading .member-route-progress {
            transform: translateX(-50%) scaleX(0.78);
            opacity: 1;
        }

        body.ccl-route-loaded .member-route-progress {
            transform: translateX(-50%) scaleX(1);
            opacity: 0;
        }
    </style>

    @include('partials.canterbury-font')
    @stack('styles')
</head>
<body class="antialiased font-display text-black">
<div class="member-route-progress" aria-hidden="true"></div>

<div class="mobile-container">
    <div class="member-shell">

        {{-- Render the page body. --}}
        @hasSection('content')
            @yield('content')
        @else
            {{ $slot }}
        @endif

        {{-- Bottom nav: shown for pages via @section('show_nav'). --}}
        @hasSection('show_nav')
            @include('layouts.bottom-nav')
        @endif
    </div>

</div>

<script>
    (() => {
        const sameOriginUrl = (value) => {
            try {
                return new URL(value, window.location.origin);
            } catch (error) {
                return null;
            }
        };

        const linkForEvent = (event) => {
            const target = event.target instanceof Element ? event.target : null;

            return target?.closest?.('a[href]');
        };

        const isNavigable = (anchor) => {
            if (!anchor || anchor.target === '_blank' || anchor.hasAttribute('download')) {
                return false;
            }

            const url = sameOriginUrl(anchor.href);

            if (!url || url.origin !== window.location.origin) {
                return false;
            }

            return url.href.split('#')[0] !== window.location.href.split('#')[0];
        };

        const showLoading = () => {
            document.body.classList.remove('ccl-route-loaded');
            document.body.classList.add('ccl-route-loading');
        };

        const hideLoading = () => {
            document.body.classList.remove('ccl-route-loading');
            document.body.classList.add('ccl-route-loaded');
            window.setTimeout(() => document.body.classList.remove('ccl-route-loaded'), 180);
        };

        const startLoading = (event) => {
            const anchor = linkForEvent(event);

            if (!isNavigable(anchor)) {
                return;
            }

            showLoading();
        };

        const startSubmitLoading = (event) => {
            const form = event.target;

            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            showLoading();
        };

        const prefetch = (event) => {
            if (window.ReactNativeWebView) {
                return;
            }

            const anchor = linkForEvent(event);

            if (!isNavigable(anchor)) {
                return;
            }

            const url = sameOriginUrl(anchor.href);
            const href = url?.href;

            const alreadyPrefetched = Array.from(document.querySelectorAll('link[rel="prefetch"]'))
                .some((link) => link.href === href);

            if (!href || alreadyPrefetched) {
                return;
            }

            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = href;
            link.as = 'document';
            document.head.appendChild(link);
        };

        window.addEventListener('pageshow', hideLoading);
        window.addEventListener('beforeunload', () => {
            if (!document.body.classList.contains('ccl-route-loading')) {
                showLoading();
            }
        });

        document.addEventListener('click', startLoading, true);
        document.addEventListener('submit', startSubmitLoading, true);
        document.addEventListener('touchstart', prefetch, { passive: true, capture: true });
        document.addEventListener('mouseover', prefetch, true);
    })();
</script>

@if (data_get(session('member'), 'id') && Route::has('notifications.stream'))
    <script>
        (() => {
            if (!('EventSource' in window)) {
                return;
            }

            const streamUrl = @js(route('notifications.stream'));
            const deviceStoreUrl = @js(route('notifications.devices.store'));
            const csrfToken = @js(csrf_token());
            const lastIdKey = 'ccl-notify-last-id';
            const shownKeyPrefix = 'ccl-notify-shown:';
            let source = null;
            let reconnectTimer = null;

            window.memberNotificationBell = (config) => ({
                unreadCount: 0,

                init() {
                    this.fetchUnreadCount();
                },

                badgeText() {
                    return this.unreadCount > 99 ? '99+' : String(this.unreadCount);
                },

                receive(notification) {
                    if (notification && notification.id) {
                        this.unreadCount += 1;
                    }
                },

                async fetchUnreadCount() {
                    try {
                        const response = await fetch(config.indexUrl, {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        });

                        if (!response.ok) {
                            return;
                        }

                        const payload = await response.json();
                        this.unreadCount = Number(payload.unread_count || 0);
                    } catch (error) {
                        console.error(error);
                    }
                },
            });

            window.memberNotificationMenu = (config) => ({
                open: false,
                loading: true,
                unreadCount: 0,
                notifications: [],

                init() {
                    this.fetchNotifications();
                },

                badgeText() {
                    return this.unreadCount > 99 ? '99+' : String(this.unreadCount);
                },

                toggle() {
                    this.open = !this.open;

                    if (this.open) {
                        this.fetchNotifications(false);
                    }
                },

                receive(notification) {
                    if (!notification || !notification.id) {
                        return;
                    }

                    const existing = this.notifications.find((item) => Number(item.id) === Number(notification.id));

                    if (existing) {
                        Object.assign(existing, notification, { read: existing.read ?? false });
                        return;
                    }

                    this.notifications.unshift(Object.assign({ read: false }, notification));
                    this.notifications = this.notifications.slice(0, 20);
                    this.unreadCount += 1;
                },

                async fetchNotifications(showLoading = true) {
                    if (showLoading) {
                        this.loading = true;
                    }

                    try {
                        const response = await fetch(config.indexUrl, {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        });

                        if (!response.ok) {
                            throw new Error('Unable to load notifications.');
                        }

                        const payload = await response.json();
                        this.unreadCount = Number(payload.unread_count || 0);
                        this.notifications = Array.isArray(payload.notifications) ? payload.notifications : [];
                    } catch (error) {
                        console.error(error);
                    } finally {
                        this.loading = false;
                    }
                },

                async markRead(notification) {
                    if (!notification || notification.read) {
                        return;
                    }

                    notification.read = true;
                    this.unreadCount = Math.max(0, this.unreadCount - 1);

                    try {
                        await fetch(config.readUrlTemplate.replace('__ID__', encodeURIComponent(notification.id)), {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });
                    } catch (error) {
                        console.error(error);
                    }
                },

                async markAllRead() {
                    const previousUnread = this.unreadCount;
                    this.unreadCount = 0;
                    this.notifications = this.notifications.map((notification) => Object.assign({}, notification, { read: true }));

                    try {
                        const response = await fetch(config.readAllUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (response.ok) {
                            const payload = await response.json();
                            this.unreadCount = Number(payload.unread_count || 0);
                        }
                    } catch (error) {
                        this.unreadCount = previousUnread;
                        console.error(error);
                    }
                },

                async openNotification(notification) {
                    await this.markRead(notification);
                    this.open = false;

                    if (notification.action_url) {
                        window.location.href = notification.action_url;
                    }
                },
            });

            const closeSource = () => {
                if (source) {
                    source.close();
                    source = null;
                }
            };

            const stack = () => {
                let element = document.getElementById('member-notify-stack');

                if (!element) {
                    element = document.createElement('div');
                    element.id = 'member-notify-stack';
                    element.className = 'member-notify-stack';
                    document.body.appendChild(element);
                }

                return element;
            };

            const notifyNativeApp = (notification) => {
                if (!window.ReactNativeWebView || typeof window.ReactNativeWebView.postMessage !== 'function') {
                    return;
                }

                window.ReactNativeWebView.postMessage(JSON.stringify({
                    type: 'ccl.notification',
                    notification,
                }));
            };

            const registerNativePushToken = async (detail) => {
                const token = String(detail?.expo_push_token || '').trim();

                if (!token) {
                    return;
                }

                try {
                    await fetch(deviceStoreUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            expo_push_token: token,
                            platform: detail?.platform || null,
                            device_id: detail?.device_id || null,
                            device_name: detail?.device_name || null,
                            app_version: detail?.app_version || null,
                        }),
                    });
                } catch (error) {
                    console.error(error);
                }
            };

            const showToast = (notification) => {
                const id = String(notification.id || '');

                if (id && sessionStorage.getItem(shownKeyPrefix + id)) {
                    return;
                }

                if (id) {
                    sessionStorage.setItem(shownKeyPrefix + id, '1');
                }

                notifyNativeApp(notification);

                if (window.ReactNativeWebView) {
                    return;
                }

                const toast = document.createElement('div');
                toast.className = 'member-notify-toast';
                toast.innerHTML = `
                    <div class="flex items-start gap-3 px-4 py-3">
                        <div class="mt-0.5 flex size-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <span class="material-symbols-outlined text-xl">notifications</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-extrabold text-black"></p>
                            <p class="mt-1 text-xs leading-5 text-black/70"></p>
                        </div>
                        <button type="button" class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary" aria-label="Dismiss notification">
                            <span class="material-symbols-outlined text-base">close</span>
                        </button>
                    </div>
                `;

                toast.querySelector('p:first-child').textContent = notification.title || 'New notification';
                toast.querySelector('p:nth-child(2)').textContent = notification.body || '';
                toast.querySelector('button').addEventListener('click', (event) => {
                    event.stopPropagation();
                    toast.remove();
                });

                if (notification.action_url) {
                    toast.addEventListener('click', () => {
                        window.location.href = notification.action_url;
                    });
                }

                stack().prepend(toast);
                window.setTimeout(() => toast.remove(), 9000);
            };

            const openSource = () => {
                if (source || document.hidden) {
                    return;
                }

                const url = new URL(streamUrl, window.location.origin);
                const lastId = Number(localStorage.getItem(lastIdKey) || 0);

                if (lastId > 0) {
                    url.searchParams.set('last_id', String(lastId));
                }

                source = new EventSource(url.toString(), { withCredentials: true });

                source.addEventListener('notification', (event) => {
                    let notification = null;

                    try {
                        notification = JSON.parse(event.data || '{}');
                    } catch (error) {
                        notification = null;
                    }

                    const eventId = Number(event.lastEventId || notification?.id || 0);

                    if (eventId > 0) {
                        localStorage.setItem(lastIdKey, String(eventId));
                    }

                    if (notification) {
                        window.dispatchEvent(new CustomEvent('ccl:notification', { detail: notification }));
                        showToast(notification);
                    }
                });

                source.onerror = () => {
                    closeSource();
                    window.clearTimeout(reconnectTimer);
                    reconnectTimer = window.setTimeout(openSource, 3500);
                };
            };

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    closeSource();
                    return;
                }

                openSource();
            });

            window.addEventListener('ccl:native-push-token', (event) => registerNativePushToken(event.detail));
            window.addEventListener('beforeunload', closeSource);
            openSource();
        })();
    </script>
@endif

</body>
</html>
