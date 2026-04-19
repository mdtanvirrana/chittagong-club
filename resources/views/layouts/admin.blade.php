<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page_title', 'Admin Panel') - {{ $companyName }}</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        admin: {
                            ink: '#0b1220',
                            panel: '#101826',
                            soft: '#172235',
                            line: '#30384a',
                            mist: '#e2e8f0',
                            gold: '#d9b24c',
                        },
                    },
                    fontFamily: {
                        display: ['Space Grotesk', 'sans-serif'],
                        body: ['Instrument Sans', 'sans-serif'],
                    },
                    boxShadow: {
                        panel: '0 18px 42px rgba(2, 6, 23, 0.22)',
                    },
                },
            },
        };
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Instrument Sans', sans-serif;
            background:
                radial-gradient(circle at top right, rgba(217, 178, 76, 0.07), transparent 20%),
                linear-gradient(180deg, #08111b 0%, #0d1724 100%);
        }
    </style>

    @stack('styles')
</head>
<body class="min-h-screen text-slate-100 antialiased">
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
        ['label' => 'Notices', 'route' => 'admin.notices.index', 'match' => 'admin.notices.*', 'icon' => 'campaign'],
        ['label' => 'Circulars', 'route' => 'admin.circulars.index', 'match' => 'admin.circulars.*', 'icon' => 'article'],
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
        class="fixed inset-y-0 left-0 z-40 flex w-[17rem] flex-col border-r border-admin-line/10 bg-admin-panel/95 px-4 py-4 shadow-panel transition-transform duration-300 lg:sticky lg:top-0 lg:h-screen"
    >
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <img src="{{ asset('logo.jpg') }}" alt="{{ $companyName }}" class="size-11 rounded-lg bg-white/5 object-contain p-1.5">
                <div>
                    <p class="font-display text-base font-bold tracking-tight text-white">Admin Panel</p>
                    <p class="text-[10px] uppercase tracking-[0.24em] text-admin-gold">{{ $companyName }}</p>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="flex size-9 items-center justify-center rounded-md border border-[#30384a] text-white/60 lg:hidden">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <nav class="mt-8 space-y-1.5">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="flex items-center gap-3 rounded-lg border px-3 py-2.5 text-sm transition-colors {{ request()->routeIs($item['match']) ? 'border-[#3b4557] bg-white/[0.05] text-white' : 'border-[#30384a] bg-white/[0.02] text-white/68 hover:border-[#3b4557] hover:bg-white/[0.04] hover:text-white' }}"
                >
                    <span class="material-symbols-outlined text-[18px] {{ request()->routeIs($item['match']) ? 'text-admin-gold' : 'text-white/45' }}">{{ $item['icon'] }}</span>
                    <span class="font-medium">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="mt-auto space-y-3">
            <a
                href="{{ route('login') }}"
                target="_blank"
                rel="noreferrer"
                class="flex items-center gap-3 rounded-lg border border-[#30384a] bg-white/[0.02] px-3 py-2.5 text-sm text-white/70 transition-colors hover:border-[#3b4557] hover:bg-white/[0.04] hover:text-white"
            >
                <span class="material-symbols-outlined text-[18px] text-white/45">open_in_new</span>
                <span class="font-medium">Open Member Login</span>
            </a>

            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-3 rounded-lg border border-[#30384a] bg-white/[0.02] px-3 py-2.5 text-sm font-medium text-white/70 transition-colors hover:border-[#3b4557] hover:bg-white/[0.04] hover:text-white"
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
                        class="absolute right-0 top-[calc(100%+0.65rem)] w-[19rem] rounded-xl border border-[#30384a] bg-[#0d1724]/95 p-4 shadow-panel backdrop-blur-xl"
                    >
                        <div class="flex items-start gap-4">
                            <span class="flex size-12 shrink-0 items-center justify-center rounded-lg border border-[#30384a] bg-admin-soft/75 font-display text-sm font-bold text-admin-gold">
                                {{ $adminInitials }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">Signed In As</p>
                                <p class="mt-1.5 font-display text-lg font-bold text-white">{{ $adminDisplayName }}</p>
                                <p class="mt-1.5 text-xs text-white/55">User ID {{ $adminId }}</p>
                                <p class="mt-1 text-xs text-white/55">{{ $adminDesignation }}</p>
                            </div>
                        </div>

                        <div class="mt-4 rounded-lg border border-[#30384a] bg-white/[0.03] px-3.5 py-3">
                            <div class="flex items-center gap-3 text-white/72">
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
