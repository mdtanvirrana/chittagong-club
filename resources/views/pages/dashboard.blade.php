@extends('layouts.app')
@section('title', 'Dashboard — Chittagong Club Ltd.')
@section('show_nav', true)

@php
    $initials = collect(explode(' ', $member->CusName))
        ->map(fn($w) => strtoupper($w[0] ?? ''))
        ->take(2)->join('');

    $fullName = trim(($member->Title ? $member->Title . ' ' : '') . $member->CusName);

    $statusColor = match(strtolower($member->MemExpTypeName ?? '')) {
        'active'  => 'text-green-400',
        'expired' => 'text-red-400',
        default   => 'text-amber-400',
    };
@endphp

@section('content')
<div
    x-data="{ sidebarOpen: false }"
    @keydown.escape.window="sidebarOpen = false"
    class="flex flex-col min-h-screen pb-24"
>

    {{-- ── Sidebar Backdrop ─────────────────────────────── --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-[60] bg-black/60 backdrop-blur-sm"
    ></div>

    {{-- ── Sidebar Panel ────────────────────────────────── --}}
    <aside
        x-show="sidebarOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed top-0 left-0 z-[70] h-full w-72 bg-[#071e33] border-r border-white/10 flex flex-col overflow-hidden"
    >
        {{-- Sidebar header --}}
        <div class="px-5 pt-5 pb-5 border-b border-white/10 bg-brand-blue/60">
            <div class="flex items-center justify-between ">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('logo.jpg') }}" alt="CCL" class="size-9 rounded-full object-contain" />
                    <div>
                        <p class="text-white font-bold text-sm leading-tight">Chittagong Club</p>
                        <p class="text-primary text-[10px] font-bold uppercase tracking-wider">Est. 1878</p>
                    </div>
                </div>
                <button @click="sidebarOpen = false"
                        class="size-8 flex items-center justify-center rounded-full bg-white/10 text-white/60">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

        </div>

        {{-- Sidebar nav --}}
        <nav class="flex-1 overflow-y-auto py-4 hide-scrollbar">

            @php
            $sidebarSections = [
                [
                    'heading' => 'Club Info',
                    'items' => [
                        ['label' => 'About CCL',           'icon' => 'info',              'route' => 'about'],
                        ['label' => 'Affiliated Clubs',    'icon' => 'handshake',         'route' => 'affiliated-clubs'],
                        ['label' => 'Contact Information', 'icon' => 'contacts',          'route' => 'contact'],
                        ['label' => 'Dress Code',          'icon' => 'checkroom',         'route' => 'dress-code'],
                        ['label' => 'General Rules',       'icon' => 'gavel',             'route' => 'general-rules'],
                    ],
                ],
                [
                    'heading' => 'Services',
                    'items' => [
                        ['label' => 'Club Shop',           'icon' => 'shopping_bag',      'route' => 'shop'],
                        ['label' => 'Gallery',             'icon' => 'photo_library',     'route' => 'gallery'],
                        ['label' => 'Greetings Calendar',  'icon' => 'calendar_month',    'route' => null],
                    ],
                ],
                [
                    'heading' => 'Members',
                    'items' => [
                        ['label' => 'Chittagong Club Limited – Executive Committee', 'icon' => 'groups', 'route' => 'executive'],
                        ['label' => 'Former Chairmen',     'icon' => 'history_edu',       'route' => 'former-chairman'],
                        ['label' => 'Employee Directory',  'icon' => 'badge',             'route' => 'employee-directory'],

                    ],
                ],
            ];
            @endphp

            @foreach ($sidebarSections as $section)
            <div class="mb-2">
                <p class="px-5 py-2 text-[10px] font-bold uppercase tracking-[0.2em] text-white/25">
                    {{ $section['heading'] }}
                </p>
                @foreach ($section['items'] as $item)
                @if ($item['route'])
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-5 py-3 text-white/70 hover:text-white hover:bg-white/5 transition-colors">
                        <span class="material-symbols-outlined text-primary text-xl shrink-0">{{ $item['icon'] }}</span>
                        <span class="text-sm font-medium">{{ $item['label'] }}</span>
                    </a>
                @else
                    <div class="flex items-center gap-3 px-5 py-3 text-white/30 cursor-not-allowed">
                        <span class="material-symbols-outlined text-white/20 text-xl shrink-0">{{ $item['icon'] }}</span>
                        <span class="text-sm font-medium">{{ $item['label'] }}</span>
                        <span class="ml-auto text-[9px] font-bold uppercase tracking-wider bg-white/10 text-white/30 px-2 py-0.5 rounded-full">Soon</span>
                    </div>
                @endif
                @endforeach
            </div>
            @endforeach
        </nav>

        {{-- Sidebar footer --}}
        <div class="px-5 py-4 border-t border-white/10 shrink-0">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 py-3 px-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20 transition-colors">
                    <span class="material-symbols-outlined text-lg">logout</span>
                    <span class="text-sm font-bold">Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- ── Sticky Top Bar ───────────────────────────────── --}}
    <header class="sticky top-0 z-50 flex items-center justify-between px-4 py-4 bg-brand-blue/90 ios-blur">
        <div class="flex items-center gap-3">
            {{-- Hamburger --}}
            <button @click="sidebarOpen = true"
                    class="size-10 flex items-center justify-center rounded-full hover:bg-white/10 transition-colors">
                <span class="material-symbols-outlined text-white">menu</span>
            </button>
            <img class="w-8 h-8 object-contain rounded-full"
                 src="{{ asset('logo.jpg') }}"
                 alt="Chittagong Club Logo" />
            <h1 class="text-base font-bold tracking-tight leading-tight">Chittagong Club Ltd.</h1>
        </div>
        <div class="flex gap-1">
            <a href="{{ route('notice-board') }}"
               class="relative p-2 rounded-full hover:bg-white/10 transition-colors">
                <span class="material-symbols-outlined text-primary">notifications</span>
                <span class="absolute top-2 right-2 flex h-2 w-2 rounded-full bg-red-500 border border-brand-blue"></span>
            </a>
        </div>
    </header>

    {{-- ── Profile Hero ─────────────────────────────────── --}}
    <section class="px-6 py-6 flex flex-col gap-6">
        <div class="flex items-center gap-5">

            <div class="relative">
                <div class="size-20 rounded-full gold-border p-1 bg-background-dark">
                    @if (file_exists(public_path('images/' . $member->PrvCusID . '.jpg')))
                        <div class="size-full rounded-full bg-center bg-cover object-center"
                             style="background-image: url('{{ asset('images/' . $member->PrvCusID . '.jpg') }}')">
                        </div>
                    @else
                        <div class="size-full rounded-full bg-primary/10 flex items-center justify-center">
                            <span class="text-primary font-extrabold text-xl">{{ $initials }}</span>
                        </div>
                    @endif
                </div>
                <div class="absolute -bottom-1 -right-1 bg-primary text-brand-blue px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider">
                    {{ strtoupper(substr($member->MemberCategory ?? 'MBR', 0, 3)) }}
                </div>
            </div>

            <div class="flex flex-col">
                <p class="text-white/60 text-sm font-medium">Welcome back,</p>
                <h2 class="text-xl font-bold tracking-tight">{{ $fullName }}</h2>
                <p class="text-primary text-sm font-semibold mt-1">{{ $member->PrvCusID }}</p>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="bg-white/10 border border-white/10 rounded-xl p-5 backdrop-blur-sm flex justify-between items-center">
            <div class="space-y-1">
                <p class="text-xs uppercase tracking-widest text-white/50">Membership</p>
                <p class="text-base font-bold {{ $statusColor }}">
                    {{ $member->MemberCategory ?? '—' }}
                </p>
                <p class="text-xs {{ $statusColor }} opacity-80">
                    {{ $member->MemExpTypeName ?? '' }}
                </p>
            </div>
            <div class="h-10 w-px bg-white/10"></div>
            <div class="space-y-1 text-right">
                <p class="text-xs uppercase tracking-widest text-white/50">Credit Balance</p>
                <p class="text-lg font-bold {{ $creditBal >= 0 ? 'text-white' : 'text-red-400' }}">
                    ৳{{ number_format(abs($creditBal), 2) }}
                </p>
                <p class="text-white/30 text-xs">
                    Due: ৳{{ number_format($totalDue, 0) }} / Limit: ৳{{ number_format($member->CreditAmt ?? 0, 0) }}
                </p>
            </div>
        </div>
    </section>

    {{-- ── Action Grid ──────────────────────────────────── --}}
    <section class="px-6 pb-8">
        <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-white/40 mb-6">Club Services</h3>
        <div class="grid grid-cols-3 gap-y-8 gap-x-4">

            @php
            $services = [
                ['route' => 'circulars',    'icon' => 'article',                'label' => 'Circular'],
                ['route' => 'notice-board', 'icon' => 'campaign',               'label' => 'Notice Board'],
                ['route' => 'ledger',       'icon' => 'account_balance_wallet', 'label' => 'Ledger'],
                ['route' => 'directory',    'icon' => 'group',                  'label' => 'Directory'],
                ['route' => 'shop',         'icon' => 'shopping_bag',           'label' => 'Club Shop'],
                ['route' => 'executive',    'icon' => 'gavel',                  'label' => 'Committee'],
                ['route' => 'contact',      'icon' => 'call',                   'label' => 'Contact'],
                ['route' => 'profile',      'icon' => 'badge',                  'label' => 'My Profile'],
            ];
            @endphp

            @foreach ($services as $s)
            <div class="flex flex-col items-center gap-3">
                <a href="{{ route($s['route']) }}"
                   class="size-20 rounded-full bg-primary/10 border border-primary/20 flex items-center justify-center transition-all active:scale-90 relative">
                    <span class="material-symbols-outlined text-primary text-3xl">{{ $s['icon'] }}</span>
                </a>
                <span class="text-xs font-semibold text-white/80 text-center tracking-tight">{{ $s['label'] }}</span>
            </div>
            @endforeach

        </div>

        {{-- Upcoming Event --}}
        <div class="mt-12">
            <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-white/40 mb-6">Upcoming Highlight</h3>
            <div class="bg-white/10 rounded-xl overflow-hidden border border-white/5">
                <div class="h-40 w-full bg-center bg-cover"
                     style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAnT7AHaLAQLOvqNQ227xQBwjRJIjM-qXBPDh0JSkhCGeeFwcnUeyeC_9rQBP1l8XTUFaVW7-aoU5SRpflqOn0-moqgF40JPYbXBm2Ipe3ruwmfduF8-CssyYPAHqx81RoRZQZnhtBChfNj4plnfKLJjJ8mZoQ9FYtohcSAxa41skc9_dcOlWYKbfjX9rTAKouok7TdZVNe8xdqhLtDtJARje4g26fdQATUOqnbOwYm5_NJj3NDhZInWfA3guJMAoIJmkmKsRq2Cao')">
                </div>
                <div class="p-5 space-y-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="text-lg font-bold">Annual Grand Gala Dinner</h4>
                            <p class="text-white/60 text-sm">Dec 24 • Grand Ballroom • 7:00 PM</p>
                        </div>
                        <div class="bg-primary text-brand-blue rounded-lg px-3 py-1 flex flex-col items-center leading-tight">
                            <span class="text-[10px] font-bold">DEC</span>
                            <span class="text-lg font-black">24</span>
                        </div>
                    </div>
                    <button class="w-full bg-primary py-3 rounded-full text-brand-blue font-bold text-sm tracking-wide transition-all active:scale-95">
                        RESERVE A TABLE
                    </button>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
