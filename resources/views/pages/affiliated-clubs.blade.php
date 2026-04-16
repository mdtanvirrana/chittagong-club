@extends('layouts.app')
@section('page_title', 'Affiliated Clubs')
@section('show_nav', true)

@section('content')
<div
    x-data="{
        search: '',
        activeClub: null,
        clubs: {{ json_encode($clubs) }},

        get filtered() {
            if (!this.search) return this.clubs;
            const q = this.search.toLowerCase();
            return this.clubs.filter(c =>
                c.name.toLowerCase().includes(q) ||
                (c.address && c.address.toLowerCase().includes(q))
            );
        },
        open(club) { this.activeClub = club; },
        close() { this.activeClub = null; }
    }"
    @keydown.escape.window="close()"
    class="flex flex-col min-h-screen pb-24"
>

    {{-- Header --}}
    <header class="sticky top-0 z-50 bg-brand-blue/90 ios-blur border-b border-white/10 px-4 pt-12 pb-4">
        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('dashboard') }}"
               class="flex size-10 items-center justify-center rounded-full hover:bg-white/10 transition-colors">
                <span class="material-symbols-outlined text-white">arrow_back_ios</span>
            </a>
            <div class="text-center">
                <p class="text-primary text-[10px] uppercase tracking-[0.2em] font-bold">{{ $companyName }}</p>
                <h1 class="text-white text-lg font-bold">Affiliated Clubs</h1>
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
                placeholder="Search clubs…"
                autocomplete="off"
                class="w-full bg-white/10 border border-white/10 rounded-full py-2.5 pl-11 pr-10 text-white placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm"
            />
            <button x-show="search" @click="search = ''"
                    class="absolute inset-y-0 right-4 flex items-center text-white/40">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
    </header>

    {{-- Count --}}
    <div class="px-4 py-3 flex items-center justify-between">
        <p class="text-white/40 text-sm">
            <span class="text-primary font-bold" x-text="filtered.length"></span> clubs
        </p>
        <p class="text-white/25 text-xs">Total: {{ count($clubs) }}</p>
    </div>

    {{-- Club list --}}
    <main class="px-4 space-y-3">

        <template x-for="club in filtered" :key="club.id">
            <button
                @click="open(club)"
                class="w-full flex items-center gap-4 bg-white/5 border border-white/10 rounded-xl p-4 text-left active:scale-[0.98] transition-transform"
            >
                {{-- Avatar / placeholder --}}
                <div class="shrink-0 size-14 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center overflow-hidden">
                    {{-- swap with <img> once images are available --}}
                    <span class="text-primary font-extrabold text-lg" x-text="club.initials"></span>
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-white font-bold text-sm leading-tight line-clamp-1" x-text="club.name"></p>
                    <p class="text-white/40 text-xs mt-0.5 line-clamp-2 leading-relaxed" x-text="club.address || 'Address not available'"></p>
                </div>

                {{-- Call icon --}}
                <template x-if="club.first_phone">
                    <a :href="'tel:' + club.first_phone.replace(/\s+/g, '')"
                       @click.stop
                       class="shrink-0 flex size-10 items-center justify-center rounded-full bg-primary/10 border border-primary/20 active:scale-90 transition-transform">
                        <span class="material-symbols-outlined text-primary text-lg">call</span>
                    </a>
                </template>
                <template x-if="!club.first_phone">
                    <div class="shrink-0 size-10 flex items-center justify-center rounded-full bg-white/5 border border-white/10">
                        <span class="material-symbols-outlined text-white/20 text-lg">phone_disabled</span>
                    </div>
                </template>
            </button>
        </template>

        {{-- Empty state --}}
        <div x-show="filtered.length === 0" class="flex flex-col items-center py-16">
            <span class="material-symbols-outlined text-5xl text-white/20 mb-3">search_off</span>
            <p class="text-white/40 text-sm">No clubs found</p>
            <button x-show="search" @click="search = ''"
                    class="mt-3 text-primary text-sm font-bold">Clear search</button>
        </div>

    </main>


    {{-- ── Detail Modal ─────────────────────────────────── --}}
    <template x-if="activeClub !== null">
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="close()"></div>

            {{-- Sheet --}}
            <div
                class="relative w-full max-w-[425px] bg-[#0a3d62] rounded-3xl border border-white/10 flex flex-col"
                style="max-height: 90dvh;"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            >
                {{-- Handle --}}
                <div class="flex justify-center pt-3 pb-1 shrink-0">
                    <div class="w-10 h-1 bg-white/20 rounded-full"></div>
                </div>

                {{-- Scrollable content --}}
                <div class="overflow-y-auto flex-1">

                    {{-- Club image / banner --}}
                    <div class="mx-4 mt-3 mb-4 h-36 rounded-2xl bg-primary/10 border border-primary/20 flex items-center justify-center overflow-hidden">
                        {{-- Replace with <img> once available --}}
                        <div class="flex flex-col items-center gap-2 opacity-40">
                            <span class="material-symbols-outlined text-4xl text-primary">domain</span>
                            <p class="text-white/40 text-xs">Image coming soon</p>
                        </div>
                    </div>

                    {{-- Name & branch --}}
                    <div class="px-5 mb-5">
                        <h2 class="text-white font-extrabold text-xl leading-tight" x-text="activeClub.name"></h2>
                        <p class="text-primary text-sm font-semibold mt-0.5" x-text="activeClub.branch"
                           x-show="activeClub.branch && activeClub.branch !== activeClub.name"></p>
                        <template x-if="activeClub.ceo">
                            <p class="text-white/40 text-xs mt-1">
                                <span class="text-white/25">CEO / Head: </span>
                                <span x-text="activeClub.ceo"></span>
                            </p>
                        </template>
                    </div>

                    {{-- Detail rows --}}
                    <div class="mx-4 bg-white/5 border border-white/10 rounded-2xl divide-y divide-white/5 mb-4">

                        {{-- Address --}}
                        <template x-if="activeClub.address">
                            <div class="flex items-start gap-3 px-4 py-3">
                                <span class="material-symbols-outlined text-primary text-base mt-0.5 shrink-0">location_on</span>
                                <p class="text-white/70 text-sm leading-relaxed" x-text="activeClub.address"></p>
                            </div>
                        </template>

                        {{-- Email --}}
                        <template x-if="activeClub.email">
                            <a :href="'mailto:' + activeClub.email"
                               class="flex items-center gap-3 px-4 py-3">
                                <span class="material-symbols-outlined text-primary text-base shrink-0">mail</span>
                                <p class="text-white/70 text-sm truncate" x-text="activeClub.email"></p>
                            </a>
                        </template>

                        {{-- Website --}}
                        <template x-if="activeClub.website">
                            <a :href="activeClub.website.startsWith('http') ? activeClub.website : 'https://' + activeClub.website"
                               target="_blank" rel="noopener"
                               class="flex items-center gap-3 px-4 py-3">
                                <span class="material-symbols-outlined text-primary text-base shrink-0">language</span>
                                <p class="text-white/70 text-sm truncate" x-text="activeClub.website"></p>
                                <span class="material-symbols-outlined text-white/20 text-sm ml-auto shrink-0">open_in_new</span>
                            </a>
                        </template>

                        {{-- Fax --}}
                        <template x-if="activeClub.fax">
                            <div class="flex items-center gap-3 px-4 py-3">
                                <span class="material-symbols-outlined text-white/30 text-base shrink-0">fax</span>
                                <p class="text-white/40 text-sm" x-text="'Fax: ' + activeClub.fax"></p>
                            </div>
                        </template>

                    </div>

                    {{-- Phone numbers --}}
                    <template x-if="activeClub.all_phones && activeClub.all_phones.length > 0">
                        <div class="mx-4 mb-6">
                            <p class="text-white/30 text-[10px] font-bold uppercase tracking-wider mb-2 px-1">Phone Numbers</p>
                            <div class="grid grid-cols-2 gap-2">
                                <template x-for="(phone, idx) in activeClub.all_phones.slice(0,6)" :key="idx">
                                    <a :href="'tel:' + phone.replace(/\s+/g, '')"
                                       class="flex items-center gap-2 bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 active:scale-95 transition-transform">
                                        <span class="material-symbols-outlined text-primary text-base shrink-0">call</span>
                                        <span class="text-white text-xs font-medium truncate" x-text="phone"></span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>

                </div>

                {{-- Close button --}}
                <div class="px-4 py-4 border-t border-white/10 shrink-0">
                    <button @click="close()"
                            class="w-full py-3 rounded-full bg-white/10 text-white/60 text-sm font-bold">
                        Close
                    </button>
                </div>

            </div>
        </div>
    </template>

</div>
@endsection
