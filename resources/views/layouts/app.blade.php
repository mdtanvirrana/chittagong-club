<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@hasSection('page_title')@yield('page_title') — {{ $companyName }}@else{{ $companyName }}@endif</title>
    @php
        $memberTheme = config('theme.member', []);
        $memberDisplayFont = data_get($memberTheme, 'fonts.display.family', 'Manrope');
        $memberDisplayWeights = data_get($memberTheme, 'fonts.display.weights', '300;400;500;600;700;800');
        $memberDisplayFontUrl = str_replace(' ', '+', $memberDisplayFont) . ':wght@' . $memberDisplayWeights;
    @endphp

    {{-- Livewire Styles --}}
    @livewireStyles

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    {{-- Alpine.js CDN (for non-Livewire pages) --}}
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
            --member-ink: {{ data_get($memberTheme, 'colors.text', '#111827') }};
            --member-ink-soft: {{ data_get($memberTheme, 'colors.text_soft', '#475569') }};
            --member-ink-muted: {{ data_get($memberTheme, 'colors.text_muted', '#64748b') }};
            --member-shell-gradient: {{ data_get($memberTheme, 'gradients.shell', 'radial-gradient(circle at top, rgba(197, 22, 46, 0.10), transparent 28%), linear-gradient(180deg, #ffffff 0%, #fff3f4 100%)') }};
            --member-accent-gradient: {{ data_get($memberTheme, 'gradients.accent', 'linear-gradient(135deg, #d61f3e 0%, #ad0f28 100%)') }};
            --member-login-gradient: {{ data_get($memberTheme, 'gradients.login', 'radial-gradient(circle at top, rgba(197, 22, 46, 0.16), transparent 34%), linear-gradient(180deg, #ffffff 0%, #fff4f5 100%)') }};
        }

        .mobile-container {
            max-width: 425px;
            margin: 0 auto;
            background: var(--member-shell-gradient);
            min-height: 100dvh;
            position: relative;
            box-shadow: 0 36px 84px -56px rgba(127, 29, 29, 0.32);
        }

        body {
            font-family: '{{ $memberDisplayFont }}', sans-serif;
            -webkit-tap-highlight-color: transparent;
            min-height: 100dvh;
            background:
                radial-gradient(circle at top, var(--member-shell-glow), transparent 28%),
                linear-gradient(180deg, var(--member-shell-start) 0%, var(--member-shell-end) 100%);
        }

        .member-shell {
            color: var(--member-ink);
            --userpanel-header-offset: 4.5rem;
        }

        .member-shell .userpanel-subheader {
            top: var(--userpanel-header-offset) !important;
            z-index: 40 !important;
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

        .member-shell [class~="text-white"] { color: var(--member-ink) !important; }
        .member-shell [class~="text-white/80"] { color: color-mix(in srgb, var(--member-ink) 82%, transparent) !important; }
        .member-shell [class~="text-white/75"] { color: color-mix(in srgb, var(--member-ink) 76%, transparent) !important; }
        .member-shell [class~="text-white/70"] { color: color-mix(in srgb, var(--member-ink-soft) 82%, transparent) !important; }
        .member-shell [class~="text-white/60"] { color: color-mix(in srgb, var(--member-ink-soft) 72%, transparent) !important; }
        .member-shell [class~="text-white/55"] { color: color-mix(in srgb, var(--member-ink-muted) 82%, transparent) !important; }
        .member-shell [class~="text-white/50"] { color: color-mix(in srgb, var(--member-ink-muted) 74%, transparent) !important; }
        .member-shell [class~="text-white/45"] { color: color-mix(in srgb, var(--member-ink-muted) 66%, transparent) !important; }
        .member-shell [class~="text-white/40"] { color: color-mix(in srgb, var(--member-ink-muted) 60%, transparent) !important; }
        .member-shell [class~="text-white/30"] { color: color-mix(in srgb, var(--member-ink-muted) 48%, transparent) !important; }
        .member-shell [class~="text-white/25"] { color: color-mix(in srgb, var(--member-ink-muted) 40%, transparent) !important; }
        .member-shell [class~="text-white/20"] { color: color-mix(in srgb, var(--member-ink-muted) 32%, transparent) !important; }
        .member-shell [class~="text-club-gold"] { color: var(--member-primary) !important; }
        .member-shell [class~="text-brand-blue"] { color: #ffffff !important; }

        .member-shell [class~="bg-[#071e33]"],
        .member-shell [class~="bg-[#0a3d62]"] {
            background: linear-gradient(180deg, var(--member-secondary) 0%, var(--member-secondary-deep) 100%) !important;
        }

        .member-shell .member-sidebar {
            background: linear-gradient(180deg, #ffffff 0%, var(--member-shell-end) 100%) !important;
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
            color: color-mix(in srgb, var(--member-primary) 78%, var(--member-ink) 22%) !important;
        }

        .member-shell .member-sidebar [class~="text-white/60"] {
            color: color-mix(in srgb, var(--member-ink-soft) 88%, transparent) !important;
        }

        .member-shell .member-sidebar [class~="text-white/50"] {
            color: color-mix(in srgb, var(--member-ink-muted) 82%, transparent) !important;
        }

        .member-shell .member-sidebar [class~="text-white/40"] {
            color: color-mix(in srgb, var(--member-ink-muted) 72%, transparent) !important;
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
        ) [class~="text-white"] { color: #ffffff !important; }
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/80"] { color: rgba(255, 255, 255, 0.82) !important; }
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/75"] { color: rgba(255, 255, 255, 0.76) !important; }
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/60"],
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/55"],
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/50"],
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/45"],
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/40"] { color: rgba(255, 255, 255, 0.62) !important; }
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/30"],
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/25"],
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-white/20"] { color: rgba(255, 255, 255, 0.4) !important; }
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-primary"] { color: #ffe1e6 !important; }
        .member-shell :is(
            [class~="bg-brand-blue"],
            [class~="bg-brand-blue/95"],
            [class~="bg-brand-blue/90"],
            [class~="bg-brand-blue/80"],
            [class~="bg-brand-blue/60"],
            [class~="bg-[#071e33]"],
            [class~="bg-[#0a3d62]"],
            [class~="blue-depth-gradient"]
        ) [class~="text-brand-blue"] { color: #ffffff !important; }

        .member-shell [class~="bg-primary"][class~="text-brand-blue"],
        .member-shell [class~="bg-primary"] [class~="text-brand-blue"] {
            color: #ffffff !important;
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
            color: rgba(255, 255, 255, 0.68) !important;
        }
    </style>

    @stack('styles')
</head>
<body class="antialiased font-display text-slate-900">

<div class="mobile-container">
    <div class="member-shell">

        {{-- Blade views use @yield, Livewire full-page uses $slot --}}
        @hasSection('content')
            @yield('content')
        @else
            {{ $slot }}
        @endif

        {{-- Bottom nav: shown for regular Blade pages via @section('show_nav')
             For Livewire pages, include it directly in the component view --}}
        @hasSection('show_nav')
            @include('layouts.bottom-nav')
        @endif
    </div>

</div>

{{-- Livewire Scripts --}}
@livewireScripts

</body>
</html>
