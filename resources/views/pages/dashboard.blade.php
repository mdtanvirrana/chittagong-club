@extends('layouts.userpanel')
@section('page_title', 'Dashboard')
@section('show_nav', true)

@php
    $initials = collect(explode(' ', $member->CusName))
        ->map(fn($w) => strtoupper($w[0] ?? ''))
        ->take(2)->join('');

    $fullName = trim(($member->Title ? $member->Title . ' ' : '') . $member->CusName);

    $statusColor = match(strtolower($member->MemExpTypeName ?? '')) {
        'active'  => 'text-primary',
        'expired' => 'text-slate-500',
        default   => 'text-slate-600',
    };
@endphp

@section('userpanel_content')
<div
    x-data="{
        previewOpen: false,
        balanceLoading: true,
        creditBal: {{ (float) ($member->CreditBal ?? 0) }},
        totalDue: null,
        creditLimit: {{ (float) ($member->CreditAmt ?? 0) }},
        async loadSummary() {
            try {
                const response = await fetch('{{ route('dashboard.summary') }}', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Failed to load dashboard summary');
                }

                const data = await response.json();
                this.creditBal = Number(data.creditBal ?? 0);
                this.totalDue = Number(data.totalDue ?? 0);
                this.creditLimit = Number(data.creditLimit ?? this.creditLimit);
            } catch (error) {
                this.totalDue = this.totalDue ?? 0;
            } finally {
                this.balanceLoading = false;
            }
        },
        formatMoney(value, decimals = 2) {
            return '' + Number(value || 0).toLocaleString(undefined, {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            });
        }
    }"
    x-init="loadSummary()"
    @keydown.escape.window="previewOpen = false"
    class="flex flex-col min-h-screen pb-24"
>
    {{-- ── Profile Hero ─────────────────────────────────── --}}
    <section class="px-6 py-6 flex flex-col gap-6">
        <div class="flex items-center gap-5">

            <div class="relative">
                <button type="button"
                        @if ($member->hasProfilePhoto) x-on:click="previewOpen = true" @endif
                        class="size-20 rounded-full gold-border p-1 bg-background-dark block"
                        :class="{ 'active:scale-95 transition-transform': {{ $member->hasProfilePhoto ? 'true' : 'false' }} }"
                        aria-label="Preview profile picture">
                    @if ($member->hasProfilePhoto)
                        <span class="relative block size-full rounded-full overflow-hidden bg-white">
                            <img src="{{ $member->profilePhotoUrl }}"
                                 alt="{{ $fullName }} profile picture"
                                 class="member-avatar-photo rounded-full">
                        </span>
                    @else
                        <span class="size-full rounded-full bg-primary/10 flex items-center justify-center">
                            <span class="text-primary font-extrabold text-xl">{{ $initials }}</span>
                        </span>
                    @endif
                </button>
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
                <p class="text-xs uppercase tracking-widest text-white/50">Total Due</p>
                <a href="{{ route('ledger') }}">
                    <div x-show="balanceLoading" class="flex justify-end">
                        <span class="inline-flex size-5 animate-spin rounded-full border-2 border-white/15 border-t-primary"></span>
                    </div>
                    <p x-show="!balanceLoading"
                       class="text-lg font-bold text-white"
                       x-text="formatMoney(totalDue ?? 0, 2)"></p>
                </a>
                <p class="text-white/50 text-xs">
                    <span x-show="balanceLoading">Remain: -- / Credit Limit: {{ number_format($member->CreditAmt ??
                    0, 0)
                    }}</span>
                    <span x-show="!balanceLoading"
                          x-text="'Remain: ' + formatMoney(creditBal ?? 0, 0) + ' / Credit Limit: ' + formatMoney(creditLimit ?? 0, 0)"></span>
                </p>
            </div>
        </div>
    </section>

    @if ($member->hasProfilePhoto)
        <div x-show="previewOpen"
             x-transition.opacity
             class="fixed inset-0 z-[80] flex items-center justify-center p-4"
             style="display: none;">
            <button type="button"
                    x-on:click="previewOpen = false"
                    class="absolute inset-0 bg-slate-950/35"
                    aria-label="Close image preview"></button>

            <div class="relative w-full max-w-sm">
                <button type="button"
                        x-on:click="previewOpen = false"
                        class="absolute right-4 top-4 z-10 flex size-10 items-center justify-center rounded-full border border-white/10 bg-slate-950/25 text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>

                <div class="member-modal-surface rounded-[2rem] border border-white/10 p-4 shadow-2xl">
                    <div class="aspect-[4/5] overflow-hidden rounded-[1.5rem] border border-primary/20 bg-white/5">
                        <img src="{{ $member->profilePhotoUrl }}"
                             alt="{{ $fullName }} full-size profile picture"
                             class="member-avatar-photo member-avatar-photo-preview">
                    </div>

                    <div class="pt-4 text-center">
                        <p class="text-white text-base font-bold">{{ $fullName }}</p>
                        <p class="mt-1 text-xs text-white/40">Member ID: {{ $member->PrvCusID }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Action Grid ──────────────────────────────────── --}}
    <section class="px-6 pb-8">
        <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-white/40 mb-6">Club Services</h3>
        <div class="grid grid-cols-3 gap-y-8 gap-x-4">

            @php
            $services = [

                ['route' => 'directory',    'icon' => 'group',                  'label' => 'Directory'],
                ['route' => 'executive',    'icon' => 'gavel',                  'label' => 'General Committee'],
                ['route' => 'ledger',       'icon' => 'account_balance_wallet', 'label' => 'Ledger'],
                ['route' => 'circulars',    'icon' => 'article',                'label' => 'Circular'],
                ['route' => 'employee-directory', 'icon' => 'badge',               'label' => 'Employee Directory'],
                ['route' => 'contact',      'icon' => 'call',                   'label' => 'Contact'],
//                ['route' => 'profile',      'icon' => 'badge',                  'label' => 'My Profile'],
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

        <div class="mt-12">
            <div class="mb-6 flex items-center justify-between gap-4">
                <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-white/40">Circular Highlight</h3>
                <a href="{{ route('circulars') }}" class="text-xs font-bold uppercase tracking-[0.16em] text-primary">View All</a>
            </div>

            @if ($dashboardHighlight)
                <div class="bg-white/10 rounded-xl overflow-hidden border border-white/5">
                    @if ($dashboardHighlight['image_url'])
                        <img
                            src="{{ $dashboardHighlight['image_url'] }}"
                            alt="{{ $dashboardHighlight['title'] }}"
                            class="h-40 w-full object-cover"
                        >
                    @endif
                    <div class="p-5 space-y-3">
                        <div class="flex justify-between items-start gap-4">
                            <div class="min-w-0">
                                <h4 class="text-lg font-bold">{{ $dashboardHighlight['title'] }}</h4>
                                <p class="mt-1 text-white/45 text-xs">Start: {{ $dashboardHighlight['start_date'] }}</p>
                                @if ($dashboardHighlight['close_date'])
                                    <p class="mt-1 text-white/45 text-xs">Close: {{ $dashboardHighlight['close_date'] }}</p>
                                @endif
                            </div>
                            <div class="bg-primary text-brand-blue rounded-lg px-3 py-1 flex flex-col items-center leading-tight shrink-0">
                                <span class="text-[10px] font-bold">{{ $dashboardHighlight['badge_month'] }}</span>
                                <span class="text-lg font-black">{{ $dashboardHighlight['badge_day'] }}</span>
                            </div>
                        </div>
                        <p class="text-sm leading-relaxed text-white/75">
                            {{ $dashboardHighlight['excerpt'] ?: 'Latest published circular from the club.' }}
                        </p>
                        <a href="{{ $dashboardHighlight['source_url'] ?: route('circulars') }}"
                           @if ($dashboardHighlight['source_url']) target="_blank" rel="noreferrer" @endif
                           class="block w-full bg-primary py-3 rounded-full text-center text-brand-blue font-bold text-sm tracking-wide transition-all active:scale-95">
                            {{ $dashboardHighlight['source_url'] ? 'OPEN LINK' : 'VIEW CIRCULAR' }}
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-white/10 rounded-xl border border-white/5 p-5">
                    <p class="text-sm font-bold text-white">No circular available</p>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Latest visible circular will appear here automatically.</p>
                </div>
            @endif
        </div>
    </section>

</div>
@endsection
