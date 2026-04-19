@extends('layouts.app')
@section('page_title', 'Circulars')
@section('show_nav', true)

@section('content')
<div class="flex min-h-screen flex-col pb-24">
    <header class="sticky top-0 z-50 border-b border-white/10 bg-brand-blue/90 px-4 pb-5 pt-12 backdrop-blur-sm">
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('dashboard') }}"
               class="flex size-10 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-white/15">
                <span class="material-symbols-outlined">arrow_back_ios</span>
            </a>
            <div class="text-center">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-primary">{{ $companyName }}</p>
                <h1 class="text-lg font-bold text-white">Circulars</h1>
            </div>
            <div class="size-10"></div>
        </div>

        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-white/75">Member Circular Archive</p>
                <p class="mt-1 text-xs text-white/45">Published directly from the admin panel.</p>
            </div>
            <div class="rounded-2xl border border-primary/20 bg-primary/10 px-3 py-2 text-right">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-primary">Showing</p>
                <p class="text-sm font-bold text-white">{{ $circulars->count() }} / {{ $circulars->total() }}</p>
            </div>
        </div>
    </header>

    <main class="flex-1 space-y-4 px-4 py-5">
        @forelse ($circulars as $circular)
            <article class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl">
                <div class="aspect-[16/9] overflow-hidden border-b border-white/8 bg-slate-950/30">
                    <img
                        src="{{ $circular['image_url'] ?: $circular['fallback_image_url'] }}"
                        alt="{{ $circular['title'] }}"
                        class="h-full w-full object-cover"
                        loading="lazy"
                        onerror="this.onerror=null;this.src=@js($circular['fallback_image_url'])"
                    >
                </div>

                <div class="border-b border-white/8 bg-[linear-gradient(135deg,rgba(242,208,13,0.12),rgba(255,255,255,0.02),rgba(12,92,139,0.18))] px-5 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-primary">Circular</p>
                            <h2 class="mt-2 text-lg font-extrabold tracking-tight text-white">{{ $circular['title'] }}</h2>
                        </div>

                        @if ($circular['source_url'])
                            <a href="{{ $circular['source_url'] }}"
                               target="_blank"
                               rel="noreferrer"
                               class="flex size-10 shrink-0 items-center justify-center rounded-full border border-white/10 bg-slate-950/20 text-white backdrop-blur-sm">
                                <span class="material-symbols-outlined text-[20px]">open_in_new</span>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="space-y-4 px-5 py-5">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-white/8 bg-slate-950/20 px-4 py-3">
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/35">Uploaded</p>
                            <p class="mt-2 text-sm font-semibold text-white">{{ $circular['uploaded_date'] }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/8 bg-slate-950/20 px-4 py-3">
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/35">Starts</p>
                            <p class="mt-2 text-sm font-semibold text-white">{{ $circular['start_date'] }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/8 bg-slate-950/20 px-4 py-3">
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/35">Closes</p>
                            <p class="mt-2 text-sm font-semibold text-white">{{ $circular['close_date'] }}</p>
                        </div>
                    </div>

                    @if ($circular['body'])
                        <p class="whitespace-pre-wrap text-sm leading-relaxed text-white/75">{{ $circular['body'] }}</p>
                    @else
                        <p class="text-sm leading-relaxed text-white/45">No additional circular details were provided.</p>
                    @endif
                </div>
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
