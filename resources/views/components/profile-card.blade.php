@props(['icon', 'title'])

<div
    x-data="{ open: false }"
    class="bg-white/10 rounded-xl border border-white/10 overflow-hidden"
>
    {{-- Header — clickable toggle --}}
    <button
        @click="open = !open"
        class="w-full flex items-center justify-between p-4 active:bg-white/5 transition-colors"
    >
        <div class="flex items-center gap-3">
            <div class="p-2 bg-primary/10 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-primary">{{ $icon }}</span>
            </div>
            <h3 class="text-white font-bold">{{ $title }}</h3>
        </div>
        <span
            class="material-symbols-outlined text-white/40 transition-transform duration-300"
            :class="open ? 'rotate-180' : ''"
        >expand_more</span>
    </button>

    {{-- Collapsible content --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="divide-y divide-white/10 border-t border-white/10"
    >
        {{ $slot }}
    </div>
</div>
