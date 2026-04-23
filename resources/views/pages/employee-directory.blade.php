@extends('layouts.userpanel')
@section('page_title', 'Employee Directory')
@section('show_nav', true)

@section('userpanel_content')
<div
    x-data="{
        search: '',
        activeBranch: 'All',
        activeEmp: null,
        previewEmp: null,
        employees: {{ json_encode($employees) }},
        grouped: {{ json_encode($grouped) }},

        get branches() {
            return ['All', ...this.grouped.map(g => g.branch)];
        },

        get filtered() {
            let list = this.employees;
            if (this.activeBranch !== 'All') {
                list = list.filter(e => e.branch === this.activeBranch);
            }
            if (this.search) {
                const q = this.search.toLowerCase();
                list = list.filter(e =>
                    e.name.toLowerCase().includes(q) ||
                    e.desig.toLowerCase().includes(q) ||
                    e.branch.toLowerCase().includes(q) ||
                    e.section.toLowerCase().includes(q)
                );
            }
            return list;
        },

        get filteredGrouped() {
            if (this.search || this.activeBranch !== 'All') return null;
            return this.grouped;
        },

        get previewOpen() {
            return this.previewEmp !== null;
        },

        open(emp) { this.activeEmp = emp; },
        close() { this.activeEmp = null; },
        openPreview(emp) {
            if (! emp || ! emp.has_photo) {
                return;
            }

            this.previewEmp = emp;
        },
        closePreview() {
            this.previewEmp = null;
        },
        handleEscape() {
            if (this.previewOpen) {
                this.closePreview();
                return;
            }

            this.close();
        }
    }"
    @keydown.escape.window="handleEscape()"
    class="flex flex-col min-h-screen pb-24"
>

    {{-- Header --}}
    <header class="userpanel-subheader bg-primary/5 pb-5 p-4 sticky top-0 z-50 rounded-b-xl shadow-lg">
        <div class="relative pb-2">
            <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-white/40 text-xl">search</span>
            </div>
            <input
                x-model="search"
                type="text"
                placeholder="Search name, role, department…"
                autocomplete="off"
                class="w-full bg-white/10 border border-white/10 rounded-full py-2.5 pl-11 pr-10 text-white placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm"
            />
            <button x-show="search" @click="search = ''"
                    class="absolute inset-y-0 right-4 flex items-center text-white/40">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>

        {{-- Branch filter chips --}}
        <div class="flex gap-2 overflow-x-auto hide-scrollbar pb-0.5">
            <template x-for="b in branches" :key="b">
                <button
                    @click="activeBranch = b; search = ''"
                    :class="activeBranch === b
                        ? 'bg-primary text-brand-blue font-bold'
                        : 'bg-white/10 text-white/60 border border-white/10'"
                    class="shrink-0 h-8 px-4 rounded-full text-xs transition-all"
                    x-text="b"
                ></button>
            </template>
        </div>
    </header>

    {{-- Stats bar --}}
    <div class="px-4 py-3 flex items-center justify-between">
       <p></p>
        <p class="text-white/40 text-sm"><span class="text-primary font-bold" x-text="filtered.length"></span> employees</p>
    </div>

    {{-- ── Grouped view (default) ──────────────────────── --}}
    <div x-show="filteredGrouped !== null" class="px-4 space-y-6 pb-4">
        <template x-for="group in grouped" :key="group.branch">
            <div>
                {{-- Branch heading --}}
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="material-symbols-outlined text-primary text-base">corporate_fare</span>
                        <h3 class="text-white font-extrabold text-sm tracking-tight" x-text="group.branch"></h3>
                    </div>
                    <div class="h-px flex-1 bg-white/10"></div>
                    <span class="text-white/25 text-xs shrink-0" x-text="group.members.length + ' staff'"></span>
                </div>

                {{-- Employee cards --}}
                <div class="space-y-2">
                    <template x-for="emp in group.members" :key="emp.id">
                        <div
                            @click="open(emp)"
                            @keydown.enter.prevent="open(emp)"
                            @keydown.space.prevent="open(emp)"
                            role="button"
                            tabindex="0"
                            class="w-full flex items-center gap-3 bg-white/5 border border-white/10 rounded-xl p-3 text-left active:scale-[0.98] transition-transform cursor-pointer"
                        >
                            {{-- Avatar --}}
                            <button
                                type="button"
                                @click.stop="openPreview(emp)"
                                class="shrink-0 size-11 rounded-xl overflow-hidden bg-primary/10 border border-primary/20 flex items-center justify-center"
                                :class="emp.has_photo ? 'active:scale-95 transition-transform' : 'cursor-default'"
                                :aria-label="'Preview ' + emp.name + ' profile picture'"
                            >
                                <img
                                    :src="emp.has_photo ? emp.photo_url : null"
                                    :alt="emp.name + ' photo'"
                                    class="h-full w-full object-cover"
                                    x-show="emp.has_photo"
                                >
                                <span class="text-primary font-extrabold text-sm" x-show="!emp.has_photo" x-text="emp.initials"></span>
                            </button>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-white font-bold text-sm leading-tight truncate" x-text="emp.name"></p>
                                <p class="text-primary/70 text-xs truncate" x-text="emp.desig"></p>
                                <p class="text-white/30 text-xs truncate" x-show="emp.section" x-text="emp.section"></p>
                            </div>

                            {{-- Blood group badge --}}
                            <div x-show="emp.blood" class="shrink-0 text-center mr-1">
                                <span class="text-[10px] font-extrabold text-red-400 bg-red-500/10 border border-red-500/20 px-2 py-0.5 rounded-full"
                                      x-text="emp.blood"></span>
                            </div>

                            {{-- Call --}}
                            <template x-if="emp.phone">
                                <a :href="'tel:' + emp.phone"
                                   @click.stop
                                   class="shrink-0 flex size-9 items-center justify-center rounded-full bg-primary/10 border border-primary/20 active:scale-90 transition-transform">
                                    <span class="material-symbols-outlined text-primary text-base">call</span>
                                </a>
                            </template>
                            <template x-if="!emp.phone">
                                <div class="shrink-0 size-9 flex items-center justify-center rounded-full bg-white/5 border border-white/5">
                                    <span class="material-symbols-outlined text-white/15 text-base">phone_disabled</span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    {{-- ── Flat filtered view (search / branch filter active) ── --}}
    <div x-show="filteredGrouped === null" class="px-4 space-y-2 pb-4">
        <template x-for="emp in filtered" :key="emp.id">
            <div
                @click="open(emp)"
                @keydown.enter.prevent="open(emp)"
                @keydown.space.prevent="open(emp)"
                role="button"
                tabindex="0"
                class="w-full flex items-center gap-3 bg-white/5 border border-white/10 rounded-xl p-3 text-left active:scale-[0.98] transition-transform cursor-pointer"
            >
                <button
                    type="button"
                    @click.stop="openPreview(emp)"
                    class="shrink-0 size-11 rounded-xl overflow-hidden bg-primary/10 border border-primary/20 flex items-center justify-center"
                    :class="emp.has_photo ? 'active:scale-95 transition-transform' : 'cursor-default'"
                    :aria-label="'Preview ' + emp.name + ' profile picture'"
                >
                    <img
                        :src="emp.has_photo ? emp.photo_url : null"
                        :alt="emp.name + ' photo'"
                        class="h-full w-full object-cover"
                        x-show="emp.has_photo"
                    >
                    <span class="text-primary font-extrabold text-sm" x-show="!emp.has_photo" x-text="emp.initials"></span>
                </button>
                <div class="flex-1 min-w-0">
                    <p class="text-white font-bold text-sm leading-tight truncate" x-text="emp.name"></p>
                    <p class="text-primary/70 text-xs truncate" x-text="emp.desig"></p>
                    <p class="text-white/30 text-xs truncate" x-text="emp.branch"></p>
                </div>
                <div x-show="emp.blood" class="shrink-0 mr-1">
                    <span class="text-[10px] font-extrabold text-red-400 bg-red-500/10 border border-red-500/20 px-2 py-0.5 rounded-full"
                          x-text="emp.blood"></span>
                </div>
                <template x-if="emp.phone">
                    <a :href="'tel:' + emp.phone" @click.stop
                       class="shrink-0 flex size-9 items-center justify-center rounded-full bg-primary/10 border border-primary/20 active:scale-90 transition-transform">
                        <span class="material-symbols-outlined text-primary text-base">call</span>
                    </a>
                </template>
                <template x-if="!emp.phone">
                    <div class="shrink-0 size-9 flex items-center justify-center rounded-full bg-white/5 border border-white/5">
                        <span class="material-symbols-outlined text-white/15 text-base">phone_disabled</span>
                    </div>
                </template>
            </div>
        </template>

        {{-- Empty --}}
        <div x-show="filtered.length === 0" class="flex flex-col items-center py-16">
            <span class="material-symbols-outlined text-5xl text-white/20 mb-3">search_off</span>
            <p class="text-white/40 text-sm">No employees found</p>
            <button @click="search = ''; activeBranch = 'All'"
                    class="mt-3 text-primary text-sm font-bold">Clear filters</button>
        </div>
    </div>


    {{-- ── Detail Modal ─────────────────────────────────── --}}
    <template x-if="activeEmp !== null">
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">

            <div class="member-modal-backdrop absolute inset-0 bg-black/60" @click="close()"></div>

            <div
                class="member-modal-surface relative w-full max-w-[425px] rounded-3xl border border-white/10 flex flex-col"
                style="max-height: 80dvh;"
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
                    <div class="flex items-center gap-4">
                        {{-- Large avatar --}}
                        <button
                            type="button"
                            @click.stop="openPreview(activeEmp)"
                            class="shrink-0 size-16 rounded-2xl overflow-hidden bg-primary/10 border border-primary/20 flex items-center justify-center"
                            :class="activeEmp.has_photo ? 'active:scale-95 transition-transform' : 'cursor-default'"
                            :aria-label="'Preview ' + activeEmp.name + ' profile picture'"
                        >
                            <img
                                :src="activeEmp.has_photo ? activeEmp.photo_url : null"
                                :alt="activeEmp.name + ' photo'"
                                class="h-full w-full object-cover"
                                x-show="activeEmp.has_photo"
                            >
                            <span class="text-primary font-extrabold text-2xl" x-show="!activeEmp.has_photo" x-text="activeEmp.initials"></span>
                        </button>
                        <div class="flex-1 min-w-0">
                            <p class="text-white font-extrabold text-base leading-tight" x-text="activeEmp.name"></p>
                            <p class="text-primary text-sm font-semibold mt-0.5" x-text="activeEmp.desig"></p>
                            <p class="text-white/40 text-xs mt-0.5" x-text="activeEmp.branch"></p>
                        </div>
                        <button @click="close()"
                                class="shrink-0 flex size-8 items-center justify-center rounded-full bg-white/10 text-white/60">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>
                </div>

                {{-- Detail rows --}}
                <div class="overflow-y-auto flex-1 px-5 py-4 space-y-2">

                    {{-- Section --}}
                    <template x-if="activeEmp.section">
                        <div class="flex items-center gap-3 bg-white/5 rounded-xl px-4 py-3">
                            <span class="material-symbols-outlined text-primary text-base shrink-0">workspaces</span>
                            <div>
                                <p class="text-white/30 text-[10px] uppercase tracking-wider">Section</p>
                                <p class="text-white text-sm" x-text="activeEmp.section"></p>
                            </div>
                        </div>
                    </template>

                    {{-- Join year --}}
                    <template x-if="activeEmp.join_year">
                        <div class="flex items-center gap-3 bg-white/5 rounded-xl px-4 py-3">
                            <span class="material-symbols-outlined text-primary text-base shrink-0">calendar_today</span>
                            <div>
                                <p class="text-white/30 text-[10px] uppercase tracking-wider">Joined</p>
                                <p class="text-white text-sm" x-text="activeEmp.join_year"></p>
                            </div>
                        </div>
                    </template>

                    {{-- Blood group --}}
                    <template x-if="activeEmp.blood">
                        <div class="flex items-center gap-3 bg-white/5 rounded-xl px-4 py-3">
                            <span class="material-symbols-outlined text-red-400 text-base shrink-0">bloodtype</span>
                            <div>
                                <p class="text-white/30 text-[10px] uppercase tracking-wider">Blood Group</p>
                                <p class="text-red-400 font-bold text-sm" x-text="activeEmp.blood"></p>
                            </div>
                        </div>
                    </template>

                    {{-- Employee ID --}}
                    <div class="flex items-center gap-3 bg-white/5 rounded-xl px-4 py-3">
                        <span class="material-symbols-outlined text-primary text-base shrink-0">badge</span>
                        <div>
                            <p class="text-white/30 text-[10px] uppercase tracking-wider">Employee ID</p>
                            <p class="text-white text-sm" x-text="activeEmp.id"></p>
                        </div>
                    </div>

                    {{-- Call button --}}
                    <template x-if="activeEmp.phone">
                        <a :href="'tel:' + activeEmp.phone"
                           class="flex items-center gap-3 bg-primary/10 border border-primary/20 rounded-xl px-4 py-3">
                            <span class="material-symbols-outlined text-primary text-base shrink-0">call</span>
                            <div>
                                <p class="text-white/30 text-[10px] uppercase tracking-wider">Mobile</p>
                                <p class="text-white text-sm" x-text="activeEmp.phone"></p>
                            </div>
                            <span class="material-symbols-outlined text-primary/40 text-base ml-auto">arrow_outward</span>
                        </a>
                    </template>

                </div>
            </div>
        </div>
    </template>

    <div x-show="previewOpen"
         x-transition.opacity
         class="fixed inset-0 z-[120] flex items-center justify-center p-4"
         style="display: none;">
        <button type="button"
                @click="closePreview()"
                class="absolute inset-0 bg-slate-950/35"
                aria-label="Close image preview"></button>

        <div class="relative w-full max-w-sm">
            <button type="button"
                    @click="closePreview()"
                    class="absolute right-4 top-4 z-10 flex size-10 items-center justify-center rounded-full border border-white/10 bg-slate-950/25 text-white">
                <span class="material-symbols-outlined">close</span>
            </button>

            <div class="member-modal-surface rounded-[2rem] border border-white/10 p-4 shadow-2xl">
                <div class="aspect-[4/5] overflow-hidden rounded-[1.5rem] border border-primary/20 bg-white/5">
                    <img :src="previewEmp ? previewEmp.photo_url : null"
                         :alt="previewEmp ? previewEmp.name + ' full-size profile picture' : 'Profile picture preview'"
                         class="size-full object-cover object-top">
                </div>

                <div class="pt-4 text-center">
                    <p class="text-white text-base font-bold" x-text="previewEmp ? previewEmp.name : ''"></p>
                    <p class="mt-1 text-xs text-white/40" x-text="previewEmp ? 'Employee ID: ' + previewEmp.id : ''"></p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
