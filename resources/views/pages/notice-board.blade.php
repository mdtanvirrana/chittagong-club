@extends('layouts.app')
@section('page_title', 'Notice Board')
@section('show_nav', true)

@section('content')
<div
    x-data="{
        search: '',
        activeNotice: null,

        get filtered() {
            if (!this.search) return notices;
            const q = this.search.toLowerCase();
            return notices.filter(n =>
                n.title.toLowerCase().includes(q) ||
                n.excerpt.toLowerCase().includes(q)
            );
        },

        openNotice(notice) { this.activeNotice = notice; },
        closeNotice() { this.activeNotice = null; }
    }"
    x-init="notices = {{ json_encode($notices) }}"
    @keydown.escape.window="closeNotice()"
    class="flex flex-col min-h-screen pb-24"
>

    {{-- Header --}}
    <header class="bg-brand-blue pt-12 pb-5 px-4 sticky top-0 z-50 rounded-b-xl shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('dashboard') }}"
               class="text-white flex size-10 items-center justify-center rounded-full hover:bg-white/10 transition-colors">
                <span class="material-symbols-outlined">arrow_back_ios</span>
            </a>
            <div class="text-center">
                <p class="text-primary text-[14px] uppercase tracking-[0.2em] font-bold">{{ $companyName }}</p>
                <h1 class="text-white text-lg font-bold">Notice Board</h1>
            </div>
            <div class="size-10"></div>
        </div>

        {{-- Search --}}
        <div class="relative">
            <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-white/40 text-xl">search</span>
            </div>
            <input
                x-model="search"
                type="text"
                placeholder="Search notices…"
                autocomplete="off"
                class="w-full bg-white/10 border border-white/10 rounded-full py-2.5 pl-11 pr-4 text-white placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm"
            />
            <button
                x-show="search"
                @click="search = ''"
                class="absolute inset-y-0 right-4 flex items-center text-white/40">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
    </header>

    {{-- Stats bar --}}
    <div class="px-4 py-3 flex items-center justify-between">
        <p class="text-white/40 text-sm">
            <span class="text-primary font-bold" x-text="filtered.length"></span>
            notices
        </p>
        <p class="text-white/30 text-xs">Total: {{ count($notices) }}</p>
    </div>

    {{-- Notices List --}}
    <main class="flex-1 px-4 space-y-3">

        <template x-for="notice in filtered" :key="notice.id">
            <button
                @click="openNotice(notice)"
                class="w-full rounded-xl border border-white/10 bg-white/5 p-4 text-left active:scale-[0.98] transition-transform"
            >
                <div class="flex items-start gap-3">
                    {{-- Icon --}}
                    <div class="shrink-0 size-10 rounded-xl bg-primary/10 flex items-center justify-center mt-0.5">
                        <span class="material-symbols-outlined text-primary text-xl">campaign</span>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-white font-bold text-sm leading-tight line-clamp-2 mb-1"
                           x-text="notice.title"></p>
                        <p class="text-white/40 text-xs leading-relaxed line-clamp-2"
                           x-text="notice.excerpt"></p>
                        <p class="text-white/25 text-[10px] mt-2 font-medium"
                           x-text="notice.date"></p>
                    </div>

                    <span class="material-symbols-outlined text-white/20 shrink-0 mt-1">chevron_right</span>
                </div>
            </button>
        </template>

        {{-- Empty state --}}
        <div x-show="filtered.length === 0" class="flex flex-col items-center justify-center py-16">
            <span class="material-symbols-outlined text-5xl text-white/20 mb-3">inbox</span>
            <p class="text-white/40 text-sm">No notices found</p>
            <button x-show="search" @click="search=''"
                    class="mt-3 text-primary text-sm font-bold">Clear search</button>
        </div>

    </main>

    {{-- ── Modal: notice detail ──────────────────────────── --}}
    <template x-if="activeNotice !== null">
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4"
             @keydown.escape.window="closeNotice()">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 ios-blur"
                 @click="closeNotice()"></div>

            {{-- Sheet --}}
            <div
                class="relative w-full max-w-[425px] bg-[#0a3d62] rounded-3xl border border-white/10 flex flex-col"
                style="max-height: 88dvh;"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            >
                {{-- Handle --}}
                <div class="flex justify-center pt-3 pb-2 shrink-0">
                    <div class="w-10 h-1 bg-white/20 rounded-full"></div>
                </div>

                {{-- Modal header --}}
                <div class="px-5 pb-4 border-b border-white/10 shrink-0">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-white font-extrabold text-base leading-snug"
                               x-text="activeNotice.title"></p>
                            <p class="text-white/40 text-xs mt-1"
                               x-text="activeNotice.date"></p>
                        </div>
                        <button @click="closeNotice()"
                                class="shrink-0 flex size-8 items-center justify-center rounded-full bg-white/10 text-white/60">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>
                </div>

                {{-- Modal body — scrollable --}}
                <div class="overflow-y-auto px-5 py-4 flex-1">
                    {{-- Render body with newlines preserved --}}
                    <p class="text-white/80 text-sm leading-relaxed whitespace-pre-wrap"
                       x-text="activeNotice.body"></p>
                </div>

            </div>
        </div>
    </template>

</div>

{{-- Pass notices to Alpine (defined before x-data evaluates) --}}
<script>
    var notices = [];
</script>
@endsection
