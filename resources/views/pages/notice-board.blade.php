@extends('layouts.app')
@section('title', 'Notice Board — Chittagong Club Ltd.')
@section('show_nav', true)

@section('content')
<div
    x-data="{
        activeFilter: 'All',
        filters: ['All', 'General', 'Events', 'Urgent', 'Finance'],
        notices: [
            { id:1, category:'Events',  title:'Annual Gala Dinner 2024', excerpt:'The Annual Grand Gala Dinner will be held on December 24th in the Grand Ballroom. Black tie dress code is mandatory.', date:'Dec 10, 2024', icon:'celebration',  urgent:false, unread:true  },
            { id:2, category:'Urgent',  title:'Temporary Pool Closure',  excerpt:'The Olympic Pool will be closed from Dec 15–20 for scheduled maintenance. We apologise for any inconvenience.', date:'Dec 8, 2024',  icon:'pool',         urgent:true,  unread:true  },
            { id:3, category:'Finance', title:'Q4 Subscription Due',     excerpt:'The Q4 membership subscription of ৳3,500 is due by December 31. Please clear dues to maintain active status.', date:'Dec 5, 2024',  icon:'payments',     urgent:false, unread:false },
            { id:4, category:'General', title:'Library New Arrivals',    excerpt:'Over 150 new titles have been added to the Grand Library catalogue this month. Members can reserve books online.', date:'Dec 1, 2024',  icon:'menu_book',    urgent:false, unread:false },
            { id:5, category:'Events',  title:'Tennis Tournament Registration', excerpt:'Registrations are now open for the Inter-Member Tennis Tournament. Last date to register: Dec 18.', date:'Nov 28, 2024', icon:'sports_tennis', urgent:false, unread:false },
        ],
        get filtered() {
            if (this.activeFilter === 'All') return this.notices;
            return this.notices.filter(n => n.category === this.activeFilter);
        }
    }"
    class="flex flex-col min-h-screen pb-24"
>
    {{-- Header --}}
    <header class="bg-brand-blue pt-12 pb-6 px-4 sticky top-0 z-50 rounded-b-xl shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('dashboard') }}" class="text-white flex size-10 items-center justify-center rounded-full hover:bg-white/10 transition-colors">
                <span class="material-symbols-outlined">arrow_back_ios</span>
            </a>
            <div class="text-center">
                <p class="text-primary text-[10px] uppercase tracking-[0.2em] font-bold">Chittagong Club Ltd</p>
                <h1 class="text-white text-lg font-bold">Notice Board</h1>
            </div>
            <button class="text-white flex size-10 items-center justify-center rounded-full hover:bg-white/10 transition-colors">
                <span class="material-symbols-outlined text-primary">search</span>
            </button>
        </div>

        {{-- Filter chips --}}
        <div class="flex gap-3 overflow-x-auto hide-scrollbar pb-1">
            <template x-for="f in filters" :key="f">
                <button
                    @click="activeFilter = f"
                    :class="activeFilter === f
                        ? 'bg-primary text-brand-blue font-bold shadow-lg shadow-primary/20'
                        : 'bg-white/10 text-white/70 border border-white/20 hover:bg-white/20'"
                    class="flex h-9 shrink-0 items-center justify-center rounded-full px-5 text-sm transition-all"
                    x-text="f"
                ></button>
            </template>
        </div>
    </header>

    {{-- Notices List --}}
    <main class="flex-1 p-4 space-y-4">

        {{-- Unread count badge --}}
        <div class="flex items-center justify-between px-1">
            <p class="text-white/50 text-sm font-medium">
                <span class="text-primary font-bold" x-text="filtered.filter(n=>n.unread).length"></span>
                unread notices
            </p>
            <button class="text-xs text-primary/70 hover:text-primary font-semibold transition-colors">Mark all read</button>
        </div>

        <template x-for="notice in filtered" :key="notice.id">
            <div
                :class="notice.unread ? 'border-primary/30 bg-white/10' : 'border-white/10 bg-white/5'"
                class="rounded-xl border p-4 transition-all active:scale-[0.98] cursor-pointer"
            >
                <div class="flex items-start gap-4">
                    {{-- Icon --}}
                    <div
                        :class="notice.urgent ? 'bg-red-500/20 text-red-400' : 'bg-primary/10 text-primary'"
                        class="shrink-0 size-12 rounded-xl flex items-center justify-center"
                    >
                        <span class="material-symbols-outlined text-2xl" x-text="notice.icon"></span>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <h3 class="text-white font-bold text-sm leading-tight truncate" x-text="notice.title"></h3>
                            <div x-show="notice.unread" class="shrink-0 size-2 rounded-full bg-primary"></div>
                        </div>
                        <p class="text-white/50 text-xs leading-relaxed line-clamp-2" x-text="notice.excerpt"></p>
                        <div class="flex items-center justify-between mt-3">
                            <span
                                :class="notice.urgent ? 'bg-red-500/20 text-red-400' : 'bg-white/10 text-white/50'"
                                class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full"
                                x-text="notice.category"
                            ></span>
                            <span class="text-white/30 text-xs" x-text="notice.date"></span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Empty state --}}
        <div x-show="filtered.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
            <span class="material-symbols-outlined text-5xl text-white/20 mb-3">inbox</span>
            <p class="text-white/40 font-medium">No notices in this category</p>
        </div>

    </main>
</div>
@endsection
