@extends('layouts.app')
@section('page_title', 'Circulars')
@section('show_nav', true)

@section('content')
<div class="flex min-h-screen flex-col pb-24">

    <header class="sticky top-0 z-50 border-b border-white/10 bg-brand-blue/90 px-4 pb-4 pt-12 backdrop-blur-sm">
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
                <p class="text-sm font-semibold text-white/75">Club Circulars</p>
            </div>
            <div class="rounded-2xl border border-primary/20 bg-primary/10 px-3 py-2 text-right">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-primary">Showing</p>
                <p class="text-sm font-bold text-white">{{ $circulars->count() }} / {{ $circulars->total() }}</p>
            </div>
        </div>
    </header>

    <main class="flex-1 space-y-5 px-4 py-5">
        @forelse ($circulars as $circular)
            <article class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] shadow-2xl">
                <div class="relative">
                    <div class="absolute inset-x-0 top-0 z-10 flex items-start justify-between gap-3 p-4">
                        <span class="rounded-full border border-white/10 bg-slate-950/30 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-white/85 backdrop-blur-sm">
                            Uploaded {{ $circular['uploaded_date'] }}
                        </span>

                        @if ($circular['source_url'])
                            <a href="{{ $circular['source_url'] }}"
                               target="_blank"
                               rel="noreferrer"
                               class="flex size-10 items-center justify-center rounded-full border border-white/10 bg-slate-950/25 text-white backdrop-blur-sm">
                                <span class="material-symbols-outlined text-[20px]">open_in_new</span>
                            </a>
                        @endif
                    </div>

                    <div class="flex min-h-[68vh] items-center justify-center bg-[radial-gradient(circle_at_top,_rgba(242,208,13,0.16),_rgba(9,37,60,0.92)_58%)] p-4 pt-20">
                        <img src="{{ $circular['image_url'] }}"
                             alt="{{ $circular['title'] }} circular poster"
                             loading="lazy"
                             class="max-h-[72vh] w-full rounded-[1.5rem] bg-white object-contain shadow-[0_24px_60px_rgba(0,0,0,0.35)]">
                    </div>
                </div>

                <div class="space-y-2 border-t border-white/10 px-5 py-4">
                    <h2 class="text-lg font-extrabold tracking-tight text-white">{{ $circular['title'] }}</h2>
                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-white/35">{{ $circular['uploaded_date_full'] }}</p>

                    @if ($circular['body'])
                        <p class="text-sm leading-relaxed text-white/65">{{ $circular['body'] }}</p>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-[2rem] border border-dashed border-white/10 bg-white/[0.03] px-6 py-16 text-center">
                <span class="material-symbols-outlined text-5xl text-primary/70">article</span>
                <p class="mt-4 text-lg font-bold text-white">No circular posters found</p>
                <p class="mt-2 text-sm leading-relaxed text-white/50">No circular entries with poster images are available right now.</p>
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

    @include('layouts.bottom-nav')
</div>
@endsection
