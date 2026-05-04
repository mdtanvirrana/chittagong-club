<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page_title', 'Admin Panel') - {{ $companyName }}</title>
    <link rel="icon" type="image/x-icon" href="{{ $companyFaviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $companyLogoUrl }}">
    @php
        $adminTheme = config('theme.admin', []);
        $adminDisplayFont = data_get($adminTheme, 'fonts.display.family', 'Space Grotesk');
        $adminDisplayWeights = data_get($adminTheme, 'fonts.display.weights', '500;700');
        $adminBodyFont = data_get($adminTheme, 'fonts.body.family', 'Instrument Sans');
        $adminBodyWeights = data_get($adminTheme, 'fonts.body.weights', '400;500;600;700');
        $adminDisplayFontUrl = str_replace(' ', '+', $adminDisplayFont) . ':wght@' . $adminDisplayWeights;
        $adminBodyFontUrl = str_replace(' ', '+', $adminBodyFont) . ':wght@' . $adminBodyWeights;
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
        $adminText = data_get($adminTheme, 'colors.text', '#111827');
        $adminTextSoft = data_get($adminTheme, 'colors.text_soft', '#475569');
        $adminTextMuted = data_get($adminTheme, 'colors.text_muted', '#64748b');
        $adminTextRgb = $hexToRgb($adminText, '17, 24, 39');
        $adminTextSoftRgb = $hexToRgb($adminTextSoft, '71, 85, 105');
        $adminTextMutedRgb = $hexToRgb($adminTextMuted, '100, 116, 139');
    @endphp

    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ $adminBodyFontUrl }}&family={{ $adminDisplayFontUrl }}&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        admin: {
                            ink: @js(data_get($adminTheme, 'colors.ink', '#0b1220')),
                            panel: @js(data_get($adminTheme, 'colors.panel', '#101826')),
                            soft: @js(data_get($adminTheme, 'colors.soft', '#172235')),
                            line: @js(data_get($adminTheme, 'colors.line', '#30384a')),
                            active: @js(data_get($adminTheme, 'colors.line_active', '#3b4557')),
                            overlay: @js(data_get($adminTheme, 'colors.overlay', '#0d1724')),
                            mist: @js(data_get($adminTheme, 'colors.mist', '#e2e8f0')),
                            gold: @js(data_get($adminTheme, 'colors.accent', '#d9b24c')),
                        },
                    },
                    fontFamily: {
                        display: [@js($adminDisplayFont), 'sans-serif'],
                        body: [@js($adminBodyFont), 'sans-serif'],
                    },
                    boxShadow: {
                        panel: @js(data_get($adminTheme, 'shadow.panel', '0 18px 42px rgba(2, 6, 23, 0.22)')),
                    },
                },
            },
        };
    </script>

    <style>
        :root {
            --admin-line: {{ data_get($adminTheme, 'colors.line', '#30384a') }};
            --admin-line-active: {{ data_get($adminTheme, 'colors.line_active', '#3b4557') }};
            --admin-overlay-95: {{ data_get($adminTheme, 'colors.overlay_95', 'rgba(13, 23, 36, 0.95)') }};
            --admin-panel-55: {{ data_get($adminTheme, 'colors.panel_55', 'rgba(16, 24, 38, 0.55)') }};
            --admin-soft-20: {{ data_get($adminTheme, 'colors.soft_20', 'rgba(23, 34, 53, 0.20)') }};
            --admin-soft-40: {{ data_get($adminTheme, 'colors.soft_40', 'rgba(23, 34, 53, 0.40)') }};
            --admin-backdrop-70: {{ data_get($adminTheme, 'colors.backdrop_70', 'rgba(11, 18, 32, 0.70)') }};
            --admin-surface: {{ data_get($adminTheme, 'colors.surface', 'rgba(255, 255, 255, 0.94)') }};
            --admin-surface-strong: {{ data_get($adminTheme, 'colors.surface_strong', 'rgba(255, 255, 255, 0.98)') }};
            --admin-text: {{ $adminText }};
            --admin-text-rgb: {{ $adminTextRgb }};
            --admin-text-soft: {{ $adminTextSoft }};
            --admin-text-soft-rgb: {{ $adminTextSoftRgb }};
            --admin-text-muted: {{ $adminTextMuted }};
            --admin-text-muted-rgb: {{ $adminTextMutedRgb }};
        }

        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: '{{ $adminBodyFont }}', sans-serif;
            background:
                radial-gradient(circle at top right, {{ data_get($adminTheme, 'colors.background_glow', 'rgba(217, 178, 76, 0.07)') }}, transparent 20%),
                linear-gradient(180deg, {{ data_get($adminTheme, 'colors.background_start', '#08111b') }} 0%, {{ data_get($adminTheme, 'colors.background_end', '#0d1724') }} 100%);
            color: var(--admin-text);
        }

        input::placeholder,
        textarea::placeholder {
            color: #94a3b8 !important;
            opacity: 1;
        }

        [class~="border-[#30384a]"] {
            border-color: var(--admin-line) !important;
        }

        [class~="border-[#3b4557]"] {
            border-color: var(--admin-line-active) !important;
        }

        [class~="bg-[#0d1724]/95"] {
            background-color: rgba(255, 255, 255, 0.97) !important;
        }

        [class~="bg-white/[0.03]"],
        [class~="bg-white/[0.04]"] {
            background-color: var(--admin-surface) !important;
        }

        [class~="bg-white/[0.05]"],
        [class~="bg-white/[0.08]"] {
            background-color: var(--admin-surface-strong) !important;
        }

        [class~="bg-slate-950/70"] {
            background-color: var(--admin-backdrop-70) !important;
        }

        [class~="bg-slate-950/55"] {
            background-color: var(--admin-panel-55) !important;
        }

        [class~="bg-slate-950/40"] {
            background-color: var(--admin-soft-40) !important;
        }

        [class~="bg-slate-950/20"] {
            background-color: var(--admin-soft-20) !important;
        }

        [class~="text-slate-100"],
        [class~="text-slate-200"],
        [class~="text-white"] {
            color: var(--admin-text) !important;
        }

        [class~="text-white/80"],
        [class~="text-white/78"],
        [class~="text-white/75"],
        [class~="text-white/72"],
        [class~="text-white/65"] {
            color: rgba(var(--admin-text-soft-rgb), 0.92) !important;
        }

        [class~="text-white/55"],
        [class~="text-white/48"],
        [class~="text-white/45"],
        [class~="text-white/40"],
        [class~="text-white/35"] {
            color: rgba(var(--admin-text-muted-rgb), 0.88) !important;
        }

        [class~="text-white/30"],
        [class~="text-white/28"],
        [class~="text-white/25"],
        [class~="text-white/20"] {
            color: rgba(var(--admin-text-muted-rgb), 0.72) !important;
        }
    </style>

    @stack('styles')
</head>
<body class="min-h-screen text-slate-900 antialiased">
@php
    $adminUser = \Illuminate\Support\Facades\Auth::guard('admin')->user();
    $adminDisplayName = $adminUser?->display_name ?? 'Admin User';
    $adminId = $adminUser?->userid ?? 'N/A';
    $adminDesignation = $adminUser?->Designation ?? 'Admin User';
    $adminNameParts = preg_split('/\s+/', trim($adminDisplayName)) ?: [];
    $adminInitials = '';

    foreach (array_slice(array_values(array_filter($adminNameParts)), 0, 2) as $part) {
        $adminInitials .= strtoupper(substr((string) $part, 0, 1));
    }

    $adminInitials = $adminInitials !== '' ? $adminInitials : 'AU';
    $headerContainerClass = trim($__env->yieldContent('header_container_class', 'w-full'));
    $mainContainerClass = trim($__env->yieldContent('main_container_class', 'w-full'));
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'space_dashboard'],
        ['label' => 'Company Profile', 'route' => 'admin.company-profile.index', 'match' => 'admin.company-profile.*', 'icon' => 'corporate_fare'],
        ['label' => 'Notices', 'route' => 'admin.notices.index', 'match' => 'admin.notices.*', 'icon' => 'campaign'],
        ['label' => 'Contacts', 'route' => 'admin.contacts.index', 'match' => 'admin.contacts.*', 'icon' => 'contacts'],
        ['label' => 'Affiliated Clubs', 'route' => 'admin.affiliated-clubs.index', 'match' => 'admin.affiliated-clubs.*', 'icon' => 'handshake'],
        ['label' => 'Circulars', 'route' => 'admin.circulars.index', 'match' => 'admin.circulars.*', 'icon' => 'article'],
        ['label' => 'Upload Pictures', 'route' => 'admin.pictures.create', 'match' => 'admin.pictures.*', 'icon' => 'upload_file'],
    ];
@endphp

<div x-data="{ sidebarOpen: false, profileOpen: false }" class="min-h-screen lg:grid lg:grid-cols-[17rem_minmax(0,1fr)]">
    <div
        x-show="sidebarOpen"
        x-transition.opacity
        @click="sidebarOpen = false"
        class="fixed inset-0 z-30 bg-slate-950/70 backdrop-blur-sm lg:hidden"
    ></div>

    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="admin-sidebar fixed inset-y-0 left-0 z-40 flex w-[17rem] flex-col border-r border-admin-line/20 bg-white/95 px-4 py-4 shadow-panel transition-transform duration-300 lg:sticky lg:top-0 lg:h-screen"
    >
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <img src="{{ $companyLogoUrl }}" alt="{{ $companyName }}" class="size-11 rounded-lg bg-admin-soft/80 object-contain p-1.5">
                <div>
                    <p class="font-display text-base font-bold tracking-tight text-slate-900">Admin Panel</p>
                    <p class="text-[10px] uppercase tracking-[0.24em] text-admin-gold">{{ $companyName }}</p>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="flex size-9 items-center justify-center rounded-md border border-admin-line/40 bg-admin-soft/60 text-admin-gold lg:hidden">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <nav class="mt-8 space-y-1.5">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="group flex items-center gap-3 rounded-lg border px-3 py-2.5 text-sm transition-colors {{ request()->routeIs($item['match']) ? 'border-admin-line/60 bg-admin-soft/80 text-admin-gold' : 'border-admin-line/35 bg-white text-slate-700 hover:border-admin-line/60 hover:bg-admin-soft/60 hover:text-admin-gold' }}"
                >
                    <span class="material-symbols-outlined text-[18px] {{ request()->routeIs($item['match']) ? 'text-admin-gold' : 'text-slate-400 group-hover:text-admin-gold' }}">{{ $item['icon'] }}</span>
                    <span class="font-medium">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="mt-auto space-y-3">
            <a
                href="{{ route('login') }}"
                target="_blank"
                rel="noreferrer"
                class="group flex items-center gap-3 rounded-lg border border-admin-line/35 bg-white px-3 py-2.5 text-sm text-slate-600 transition-colors hover:border-admin-line/60 hover:bg-admin-soft/60 hover:text-admin-gold"
            >
                <span class="material-symbols-outlined text-[18px] text-slate-400 group-hover:text-admin-gold">open_in_new</span>
                <span class="font-medium">Open Member Login</span>
            </a>

            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-3 rounded-lg border border-admin-line/40 bg-admin-soft/90 px-3 py-2.5 text-sm font-medium text-admin-gold transition-colors hover:bg-admin-soft"
                >
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="min-w-0">
        <header class="sticky top-0 z-20 border-b border-admin-line/10 bg-slate-950/55 backdrop-blur-xl">
            <div class="{{ $headerContainerClass }} flex items-center justify-between gap-4 px-3 py-3 lg:px-6">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="flex size-10 items-center justify-center rounded-md border border-[#30384a] bg-white/[0.03] lg:hidden">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.24em] text-admin-gold">@yield('page_eyebrow', 'Operations')</p>
                        <h1 class="font-display text-xl font-bold tracking-tight text-white lg:text-[1.35rem]">@yield('page_title', 'Admin Panel')</h1>
                    </div>
                </div>

                <div
                    class="relative"
                    @mouseenter="profileOpen = true"
                    @mouseleave="profileOpen = false"
                >
                    <button
                        type="button"
                        @click="profileOpen = ! profileOpen"
                        :aria-expanded="profileOpen.toString()"
                        aria-haspopup="true"
                        class="flex items-center gap-3 rounded-lg border border-[#30384a] bg-white/[0.03] px-3 py-2 text-left transition hover:border-[#3b4557] hover:bg-white/[0.05]"
                    >
                        <div class="hidden text-right sm:block">
                            <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">Signed In As</p>
                            <p class="mt-0.5 max-w-[10rem] truncate text-xs font-semibold text-white">{{ $adminDisplayName }}</p>
                        </div>
                        <span class="flex size-10 items-center justify-center rounded-lg border border-[#30384a] bg-admin-soft/80 font-display text-xs font-bold text-admin-gold">
                            {{ $adminInitials }}
                        </span>
                    </button>

                    <div
                        x-cloak
                        x-show="profileOpen"
                        x-transition.origin.top.right
                        @click.outside="profileOpen = false"
                        class="absolute right-0 top-[calc(100%+0.65rem)] w-[19rem] rounded-xl border border-admin-line/35 bg-[#0d1724]/95 p-4 shadow-panel backdrop-blur-xl"
                    >
                        <div class="flex items-start gap-4">
                            <span class="flex size-12 shrink-0 items-center justify-center rounded-lg border border-admin-line/35 bg-admin-soft/75 font-display text-sm font-bold text-admin-gold">
                                {{ $adminInitials }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400">Signed In As</p>
                                <p class="mt-1.5 font-display text-lg font-bold text-slate-900">{{ $adminDisplayName }}</p>
                                <p class="mt-1.5 text-xs text-slate-500">User ID {{ $adminId }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $adminDesignation }}</p>
                            </div>
                        </div>

                        <div class="mt-4 rounded-lg border border-admin-line/35 bg-admin-soft/60 px-3.5 py-3">
                            <div class="flex items-center gap-3 text-slate-600">
                                <span class="material-symbols-outlined text-[18px] text-admin-gold">settings</span>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em]">Profile Settings</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="{{ $mainContainerClass }} px-3 py-4 lg:px-6 lg:py-5">
            @if (session('status'))
                <div class="mb-5 rounded-lg border border-admin-line/12 bg-white/[0.04] px-4 py-3 text-xs text-white/75">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
