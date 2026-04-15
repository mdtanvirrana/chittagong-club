<div
    class="flex flex-col min-h-screen pb-24"
    x-data="{
        previewOpen: false,
        previewName: '',
        previewId: '',
        previewUrl: null,
        init() {
            // Intersection Observer watches the sentinel div at bottom
            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    @this.loadMore()
                }
            }, { threshold: 0.5 });

            this.$nextTick(() => {
                const sentinel = document.getElementById('scroll-sentinel');
                if (sentinel) observer.observe(sentinel);
            });

            // Re-observe after each Livewire update (new items rendered)
            $wire.on('render', () => {
                this.$nextTick(() => {
                    const sentinel = document.getElementById('scroll-sentinel');
                    if (sentinel) observer.observe(sentinel);
                });
            });
        },
        openPreview(name, id, url) {
            if (!url) return;
            this.previewName = name;
            this.previewId = id;
            this.previewUrl = url;
            this.previewOpen = true;
        },
        closePreview() {
            this.previewOpen = false;
            this.previewName = '';
            this.previewId = '';
            this.previewUrl = null;
        }
    }"
    x-on:keydown.escape.window="closePreview()"
>
    {{-- Sticky Header --}}
    <header class="sticky top-0 z-50 bg-background-dark/95 ios-blur border-b border-white/10">
        <div class="flex items-center p-4 justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center justify-center size-10 rounded-full hover:bg-white/10 transition-colors">
                    <span class="material-symbols-outlined text-white">arrow_back_ios</span>
                </a>
                <h1 class="text-xl font-extrabold tracking-tight">Member Directory</h1>
            </div>
            <div class="text-white/40 text-xs">{{ $total }} members</div>
        </div>

        {{-- Search --}}
        <div class="px-4 pb-3">
            <div class="relative">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined text-white/40 text-xl">search</span>
                </div>
                <input
                    wire:model.live.debounce.400ms="search"
                    type="text"
                    placeholder="Search by name or ID…"
                    class="w-full bg-white/10 border border-white/10 rounded-full py-3 pl-12 pr-10 text-white placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary/30 transition-all text-sm"
                />
                <div wire:loading wire:target="search" class="absolute inset-y-0 right-4 flex items-center">
                    <svg class="animate-spin h-4 w-4 text-primary" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Filter chips --}}
        <div class="flex gap-2 px-4 pb-4 overflow-x-auto hide-scrollbar">
            <button
                wire:click="$set('activeFilter', 'All')"
                class="flex h-8 shrink-0 items-center rounded-full px-4 text-xs transition-all
                       {{ $activeFilter === 'All' ? 'bg-primary text-brand-blue font-bold' : 'bg-white/10 text-white/60 border border-white/10' }}"
            >All</button>

            @foreach ($categories as $cat)
                <button
                    wire:click="$set('activeFilter', '{{ $cat }}')"
                    class="flex h-8 shrink-0 items-center rounded-full px-4 text-xs transition-all
                       {{ $activeFilter === $cat ? 'bg-primary text-brand-blue font-bold' : 'bg-white/10 text-white/60 border border-white/10' }}"
                >{{ $cat }}</button>
            @endforeach
        </div>
    </header>

    {{-- Stats bar --}}
    <div class="px-4 py-3">
        <p class="text-white/40 text-sm">
            Showing <span class="text-primary font-bold">{{ $members->count() }}</span>
            of <span class="font-bold text-white/60">{{ $total }}</span> members
        </p>
    </div>

    {{-- Members List --}}
    <main class="flex-1 px-4 space-y-3">

        @forelse ($members as $member)
            @php
                $initials = collect(explode(' ', $member->CusName))
                    ->map(fn($w) => strtoupper($w[0] ?? ''))
                    ->take(2)->join('');
            @endphp

            <a href="{{ route('directory.show', $member->PrvCusID) }}"
               class="flex items-center gap-4 bg-white/5 border border-white/10 rounded-xl p-4 active:scale-[0.98] transition-transform">

                @php
                    $memberPhotoUrl = \App\Support\PortalCache::memberPhotoUrl($member->PrvCusID);
                @endphp
                <div class="shrink-0 size-14 rounded-full overflow-hidden border-2 border-primary/30 bg-primary/10 flex items-center justify-center">
                    @if ($memberPhotoUrl)
                        <button type="button"
                                x-on:click.prevent.stop="openPreview(@js($member->CusName), @js($member->PrvCusID), @js($memberPhotoUrl))"
                                class="size-full rounded-full overflow-hidden active:scale-95 transition-transform"
                                aria-label="Preview {{ $member->CusName }} profile picture">
                            <img class="size-full rounded-full object-cover object-top"
                                 src="{{ $memberPhotoUrl }}"
                                 alt="Profile Image">
                        </button>
                    @else
                        <div class="size-full rounded-full bg-primary/10 flex items-center justify-center">
                            <span class="text-primary font-bold text-sm">{{ $initials }}</span>
                        </div>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-white font-bold text-sm truncate">{{ $member->CusName }}</p>
                    <p class="text-white/30 text-xs mt-0.5">ID: {{ $member->PrvCusID }}</p>
                </div>

                <span class="material-symbols-outlined text-white/30 text-lg shrink-0">chevron_right</span>
            </a>
        @empty
            <div class="flex flex-col items-center py-16">
                <span class="material-symbols-outlined text-5xl text-white/20 mb-3">person_search</span>
                <p class="text-white/40 text-sm">No members match your search</p>
            </div>
        @endforelse

    </main>

    {{-- Scroll sentinel + loading indicator --}}
    @if ($hasMore)
        <div id="scroll-sentinel" class="flex justify-center py-6">
            <div wire:loading.flex wire:target="loadMore" class="items-center gap-2 text-white/40 text-sm">
                <svg class="animate-spin h-4 w-4 text-primary" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                Loading more…
            </div>
            {{-- Invisible trigger point when not loading --}}
            <div wire:loading.remove wire:target="loadMore" class="h-1 w-1"></div>
        </div>
    @else
        <p class="text-center text-white/20 text-xs py-6">All {{ $total }} members loaded</p>
    @endif

    <div x-show="previewOpen"
         x-transition.opacity
         class="fixed inset-0 z-[80] flex items-center justify-center p-4"
         style="display: none;">
        <button type="button"
                x-on:click="closePreview()"
                class="absolute inset-0 bg-slate-950/35"
                aria-label="Close image preview"></button>

        <div class="relative w-full max-w-sm">
            <button type="button"
                    x-on:click="closePreview()"
                    class="absolute right-4 top-4 z-10 flex size-10 items-center justify-center rounded-full border border-white/10 bg-slate-950/25 text-white">
                <span class="material-symbols-outlined">close</span>
            </button>

            <div class="rounded-[2rem] border border-white/10 bg-brand-blue/90 p-4 shadow-2xl">
                <div class="aspect-[4/5] overflow-hidden rounded-[1.5rem] border border-primary/20 bg-white/5">
                    <img :src="previewUrl"
                         :alt="previewName ? previewName + ' full-size profile picture' : 'Profile picture preview'"
                         class="size-full object-cover object-top">
                </div>

                <div class="pt-4 text-center">
                    <p class="text-white text-base font-bold" x-text="previewName"></p>
                    <p class="mt-1 text-xs text-white/40" x-text="previewId ? 'Member ID: ' + previewId : ''"></p>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.bottom-nav')
</div>
