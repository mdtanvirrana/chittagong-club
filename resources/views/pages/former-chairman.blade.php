@extends('layouts.userpanel')
@section('page_title', 'Former Chairmen')
@section('show_nav', true)

@section('userpanel_content')
<div class="flex flex-col min-h-screen pb-24">


    <main class="flex flex-col gap-6 p-4 pb-8">

        {{-- Title --}}
        <div class="flex flex-col items-center justify-center py-2">
            <div class="flex items-center gap-2 mb-2">
                <span class="h-px w-8 bg-primary/40"></span>
                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Legacy of Leadership</span>
                <span class="h-px w-8 bg-primary/40"></span>
            </div>
            <p class="text-white/40 text-sm">
                {{ $grouped->sum(fn($g) => count($g['members'])) }} Chairmen across {{ $grouped->count() }} terms
            </p>
        </div>

        @forelse ($grouped as $group)

        {{-- Year group heading --}}
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 shrink-0">
                <span class="material-symbols-outlined text-primary text-base">history_edu</span>
                <h3 class="text-white font-extrabold text-base tracking-tight">{{ $group['label'] }}</h3>
            </div>
            <div class="h-px flex-1 bg-white/10"></div>
        </div>

        {{-- Member cards --}}
<div class="grid gap-3 -mt-3 w-full">
    @foreach ($group['members'] as $m)
    {{-- Main Card: Added overflow-hidden and backdrop blur --}}
    <div class="flex items-center gap-3 bg-brand-blue/20 backdrop-blur-md border border-white/10 rounded-2xl p-3 overflow-hidden shadow-lg">

        {{-- Avatar: shrink-0 ensures the circle doesn't turn into an oval --}}
        <div class="shrink-0 size-14 rounded-full overflow-hidden border border-white/10 shadow-inner">
            @if ($m['has_photo'])
                <img src="{{ $m['photo_url'] }}" alt="{{ $m['name'] }}" class="member-avatar-photo">
            @else
                <div class="size-full bg-primary/10 flex items-center justify-center">
                    <span class="text-primary font-extrabold text-base">{{ $m['initials'] }}</span>
                </div>
            @endif
        </div>

        {{-- Info: min-w-0 is the "Magic Fix" for text overflow --}}
        <div class="flex-1 min-w-0">
            {{-- Serial + Name --}}
            <div class="flex items-center gap-1.5 mb-0.5">
                <span class="text-primary/60 text-[9px] font-black shrink-0">#{{ $m['serial'] }}</span>
                <h4 class="text-white font-bold text-sm truncate uppercase tracking-tight">
                    {{ $m['name'] }}
                </h4>
            </div>

            {{-- Designation --}}
            @if ($m['designation'])
            <div class="flex items-center gap-1 mb-1">
                <span class="material-symbols-outlined text-primary text-[14px]">business_center</span>
                <p class="text-primary text-[11px] font-bold truncate uppercase tracking-wide">
                    {{ $m['designation'] }}
                </p>
            </div>
            @endif

            {{-- Area / Term Description --}}
            @if ($m['area'])
            <p class="text-black text-[12px] line-clamp-2 ">
                {{ $m['area'] }}
            </p>
            @endif
        </div>

        {{-- Call button: shrink-0 and ml-auto keeps it pinned to the right --}}
        <div class="shrink-0 ml-auto">
            @if ($m['phone'])
            <a href="tel:{{ preg_replace('/\s+/', '', $m['phone']) }}"
               class="flex size-10 items-center justify-center rounded-full bg-white/5 border border-white/10 active:bg-primary/20 transition-all shadow-md">
                <span class="material-symbols-outlined text-primary text-lg">call</span>
            </a>
            @else
            <div class="size-10 flex items-center justify-center rounded-full bg-white/5 border border-white/5 opacity-20">
                <span class="material-symbols-outlined text-white text-lg">phone_disabled</span>
            </div>
            @endif
        </div>

    </div>
    @endforeach
</div>

        @empty
        <div class="flex flex-col items-center justify-center py-20">
            <span class="material-symbols-outlined text-5xl text-white/20 mb-3">history_edu</span>
            <p class="text-white/40 text-sm">No records found</p>
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
