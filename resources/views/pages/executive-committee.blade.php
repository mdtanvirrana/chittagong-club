@extends('layouts.app')
@section('page_title', 'Executive Committee')
@section('show_nav', true)

@section('content')
<div class="flex flex-col min-h-screen pb-24">

    {{-- Sticky Header --}}
    <header class="sticky top-0 z-50 bg-brand-blue/90 ios-blur flex items-center px-4 pt-12 pb-4 justify-between border-b border-white/10">
        <a href="{{ route('dashboard') }}"
           class="text-white flex size-10 items-center justify-center rounded-full hover:bg-white/10 cursor-pointer">
            <span class="material-symbols-outlined">arrow_back_ios_new</span>
        </a>
        <div class="text-center">
            <p class="text-primary text-[10px] uppercase tracking-[0.2em] font-bold">{{ $companyName }}</p>
            <h1 class="text-white text-lg font-bold">{{ $companyName }} – General Committee</h1>
        </div>
        <div class="size-10"></div>
    </header>

    <main class="flex flex-col gap-6 p-4 pb-8">

        {{-- Title --}}
        <div class="flex flex-col items-center justify-center py-2">
            <div class="flex items-center gap-2 mb-2">
                <span class="h-px w-8 bg-primary/40"></span>
                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Leadership</span>
                <span class="h-px w-8 bg-primary/40"></span>
            </div>
            <p class="text-white/40 text-sm font-medium">
                Showing {{ $previousYear }} – {{ $currentYear }} committee members
            </p>
        </div>

        @forelse ($grouped as $group)

        {{-- Year group heading --}}
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 shrink-0">
                <span class="material-symbols-outlined text-primary text-base">gavel</span>
                <h3 class="text-white font-extrabold text-base tracking-tight">{{ $group['label'] }}</h3>
            </div>
            <div class="h-px flex-1 bg-white/10"></div>
            <span class="text-white/30 text-xs shrink-0">{{ count($group['members']) }} members</span>
        </div>

        {{-- Member cards --}}
        {{-- Member cards container --}}
<div class="grid gap-3 -mt-3 w-full">
    @foreach ($group['members'] as $m)
    {{-- Container: removed fixed widths, using flex-1 to let it breathe --}}
    <div class="flex items-center gap-3 bg-white/5 border border-white/10 rounded-2xl p-3 sm:p-4 overflow-hidden">

        {{-- Avatar: Shrink-0 prevents the image from squishing --}}
        <div class="shrink-0 size-14 sm:size-16 rounded-xl overflow-hidden border border-primary/20">
            @if ($m['has_photo'])
                <div class="size-full bg-center bg-cover"
                     style="background-image: url('{{ asset('images/' . $m['member_id'] . '.jpg') }}')">
                </div>
            @else
                <div class="size-full bg-primary/10 flex items-center justify-center">
                    <span class="text-primary font-extrabold text-base">{{ $m['initials'] }}</span>
                </div>
            @endif
        </div>

        {{-- Info: min-w-0 is CRITICAL here to allow truncate/line-clamp to work inside flex --}}
        <div class="flex-1 min-w-0">
            {{-- Name --}}
            <h4 class="text-white font-bold text-sm leading-tight truncate uppercase tracking-tight">
                {{ $m['name'] }}
            </h4>

            {{-- Designation --}}
            @if ($m['designation'])
            <div class="flex items-center gap-1 mt-0.5">
                <span class="material-symbols-outlined text-primary text-[14px]">business_center</span>
                <p class="text-primary text-[11px] font-bold truncate uppercase">{{ $m['designation'] }}</p>
            </div>
            @endif

            {{-- Area: Description --}}
            @if ($m['area'])
            <p class="text-white/40 text-[10px] leading-snug line-clamp-2 mt-1 italic">
                {{ $m['area'] }}
            </p>
            @endif
        </div>

        {{-- Call button: Kept at a consistent size --}}
        <div class="shrink-0 ml-auto">
            @if ($m['phone'])
            <a href="tel:{{ preg_replace('/\s+/', '', $m['phone']) }}"
               class="flex size-10 items-center justify-center rounded-full bg-white/5 border border-white/10 active:bg-primary/20 transition-colors">
                <span class="material-symbols-outlined text-primary text-xl">call</span>
            </a>
            @else
            <div class="size-10 flex items-center justify-center rounded-full bg-white/5 border border-white/5 opacity-20">
                <span class="material-symbols-outlined text-white text-xl">phone_disabled</span>
            </div>
            @endif
        </div>

    </div>
    @endforeach
</div>

        @empty
        <div class="flex flex-col items-center justify-center py-20">
            <span class="material-symbols-outlined text-5xl text-white/20 mb-3">group_off</span>
            <p class="text-white/40 text-sm">No committee members found</p>
        </div>
        @endforelse

        {{-- Footer --}}
        <div class="mt-4 flex flex-col items-center text-center opacity-40 px-8">
            <span class="material-symbols-outlined text-2xl mb-2 text-primary">verified_user</span>
            <p class="text-xs italic">Upholding the prestige and legacy of {{ $companyName }}</p>
        </div>

    </main>
</div>
@endsection
