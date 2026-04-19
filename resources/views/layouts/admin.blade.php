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
                            ink: '#07131d',
                            panel: '#0d2233',
                            soft: '#122d41',
                            line: '#1c425d',
                            gold: '#f0c441',
                            teal: '#64d2c7',
                        },
                    },
                    fontFamily: {
                        display: ['Space Grotesk', 'sans-serif'],
                        body: ['Instrument Sans', 'sans-serif'],
                    },
                    boxShadow: {
                        panel: '0 28px 60px rgba(4, 11, 17, 0.28)',
                    },
                },
            },
        };
    </script>

    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(100, 210, 199, 0.10), transparent 28%),
                radial-gradient(circle at top right, rgba(240, 196, 65, 0.10), transparent 24%),
                linear-gradient(180deg, #07131d 0%, #0a1b28 100%);
        }
    </style>

    @stack('styles')
</head>
<body class="min-h-screen text-slate-100 antialiased">
@php
    $adminUser = \Illuminate\Support\Facades\Auth::guard('admin')->user();
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'space_dashboard'],
        ['label' => 'Notices', 'route' => 'admin.notices.index', 'match' => 'admin.notices.*', 'icon' => 'campaign'],
        ['label' => 'Circulars', 'route' => 'admin.circulars.index', 'match' => 'admin.circulars.*', 'icon' => 'article'],
    ];
@endphp

<div x-data="{ sidebarOpen: false }" class="min-h-screen lg:grid lg:grid-cols-[18.5rem_minmax(0,1fr)]">
    <div
        x-show="sidebarOpen"
        x-transition.opacity
        @click="sidebarOpen = false"
        class="fixed inset-0 z-30 bg-slate-950/70 backdrop-blur-sm lg:hidden"
    ></div>

    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-40 flex w-[18.5rem] flex-col border-r border-admin-line/80 bg-admin-panel/95 px-5 py-5 shadow-panel transition-transform duration-300 lg:sticky lg:top-0 lg:h-screen"
    >
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <img src="{{ asset('logo.jpg') }}" alt="{{ $companyName }}" class="size-12 rounded-2xl bg-white/5 object-contain p-1.5">
                <div>
                    <p class="font-display text-lg font-bold tracking-tight text-white">Admin Panel</p>
                    <p class="text-xs uppercase tracking-[0.24em] text-admin-gold">{{ $companyName }}</p>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="flex size-10 items-center justify-center rounded-2xl border border-white/10 text-white/60 lg:hidden">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="mt-8 rounded-[1.75rem] border border-white/8 bg-white/[0.03] p-4">
            <p class="text-xs uppercase tracking-[0.2em] text-white/35">Signed In As</p>
            <p class="mt-2 font-display text-xl font-bold text-white">{{ $adminUser?->display_name ?? 'Admin' }}</p>
            <div class="mt-3 flex flex-wrap gap-2 text-xs text-white/65">
                <span class="rounded-full border border-white/10 px-3 py-1">{{ $adminUser?->userid ?? 'N/A' }}</span>
                <span class="rounded-full border border-white/10 px-3 py-1">{{ $adminUser?->Designation ?? 'Admin User' }}</span>
            </div>
        </div>

        <nav class="mt-8 space-y-2">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="flex items-center gap-3 rounded-2xl border px-4 py-3 transition-colors {{ request()->routeIs($item['match']) ? 'border-admin-gold/35 bg-admin-gold/10 text-white' : 'border-white/8 bg-white/[0.02] text-white/70 hover:border-white/15 hover:bg-white/[0.05] hover:text-white' }}"
                >
                    <span class="material-symbols-outlined {{ request()->routeIs($item['match']) ? 'text-admin-gold' : 'text-white/45' }}">{{ $item['icon'] }}</span>
                    <span class="font-medium">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="mt-auto space-y-3">
            <a
                href="{{ route('login') }}"
                target="_blank"
                rel="noreferrer"
                class="flex items-center gap-3 rounded-2xl border border-white/8 bg-white/[0.02] px-4 py-3 text-white/70 transition-colors hover:border-white/15 hover:bg-white/[0.05] hover:text-white"
            >
                <span class="material-symbols-outlined text-white/45">open_in_new</span>
                <span class="font-medium">Open Member Login</span>
            </a>

            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-3 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 font-medium text-red-200 transition-colors hover:bg-red-500/15"
                >
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="min-w-0">
        <header class="sticky top-0 z-20 border-b border-white/8 bg-slate-950/55 backdrop-blur-xl">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 lg:px-8">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="flex size-11 items-center justify-center rounded-2xl border border-white/10 bg-white/[0.03] lg:hidden">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-admin-gold">@yield('page_eyebrow', 'Operations')</p>
                        <h1 class="font-display text-2xl font-bold tracking-tight text-white">@yield('page_title', 'Admin Panel')</h1>
                    </div>
                </div>

                <div class="hidden rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3 text-right lg:block">
                    <p class="text-xs uppercase tracking-[0.18em] text-white/35">Portal Sync</p>
                    <p class="text-sm font-semibold text-white/85">Admin updates flow into the member panel</p>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-6 lg:px-8 lg:py-8">
            @if (session('status'))
                <div class="mb-6 rounded-[1.5rem] border border-emerald-400/20 bg-emerald-500/10 px-5 py-4 text-sm text-emerald-100">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
