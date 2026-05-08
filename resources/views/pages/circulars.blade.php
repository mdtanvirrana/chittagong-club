@extends('layouts.userpanel')
@section('page_title', 'Circulars')
@section('show_nav', true)

@section('userpanel_content')
<div class="flex min-h-screen flex-col pb-24">
    <main class="flex-1 space-y-4 px-4 py-5">
        @forelse ($circulars as $circular)
            @php($imageUrl = $circular['display_image_url'] ?? $circular['image_url'] ?? null)
            @php($dateLabel = $circular['date_label'] ?? ($circular['close_date'] ?? $circular['start_date'] ?? ''))
            <article class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl">
                <div class="py-2 px-3 flex justify-between">
                        <h2 class="mt-2 text-md font-extrabold tracking-tight text-white">{{ $circular['title'] }}</h2>
                    <div class="text-sm mt-2 ">
                        {{ $dateLabel }}
                    </div>
                </div>

                @if ($imageUrl)
                    <div class="overflow-hidden border-b border-white/8">
                        <img
                            src="{{ $imageUrl }}"
                            alt="{{ $circular['title'] }}"
                            class="block h-auto w-full"
                            loading="lazy"
                        >
                    </div>
                @endif
            </article>
        @empty
            <div class="rounded-[2rem] border border-dashed border-white/10 bg-white/[0.03] px-6 py-16 text-center">
                <span class="material-symbols-outlined text-5xl text-primary/70">article</span>
                <p class="mt-4 text-lg font-bold text-white">No circulars available</p>
                <p class="mt-2 text-sm leading-relaxed text-white/50">Published circulars will appear here as soon as they are made visible from the admin panel.</p>
            </div>
        @endforelse

        @if ($circulars->hasPages())
            <div class="grid grid-cols-2 gap-3 pt-2">
                @if ($circulars->onFirstPage())
                    <div class="rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-center text-sm font-semibold text-white/25">
                        Newer Circulars
                    </div>
                @else
                    <a href="{{ $circulars->previousPageUrl() }}"
                       class="rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3 text-center text-sm font-semibold text-white transition-colors hover:bg-white/[0.08]">
                        Newer Circulars
                    </a>
                @endif

                @if ($circulars->hasMorePages())
                    <a href="{{ $circulars->nextPageUrl() }}"
                       class="rounded-2xl border border-primary/20 bg-primary/10 px-4 py-3 text-center text-sm font-bold text-primary transition-colors hover:bg-primary/15">
                        Older Circulars
                    </a>
                @else
                    <div class="rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-center text-sm font-semibold text-white/25">
                        End of Archive
                    </div>
                @endif
            </div>
        @endif
    </main>
</div>
@endsection
