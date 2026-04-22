@extends('layouts.userpanel')
@section('page_title', 'Member Directory')
@section('show_nav', true)
@section('userpanel_content')

<div x-data="memberDirectory()" x-on:keydown.escape.window="closePreview()" class="flex flex-col min-h-screen pb-24">

    {{-- Header --}}
    <header class="userpanel-subheader sticky top-0 z-50 bg-brand-blue/90 ios-blur border-b border-white/10">
        <div class="flex items-center px-4 pt-12 pb-3 justify-between">
            <a href="{{ route('dashboard') }}" class="size-10 flex items-center justify-center rounded-full hover:bg-white/10">
                <span class="material-symbols-outlined text-white">arrow_back_ios</span>
            </a>
            <div class="text-center">
                <p class="text-primary text-[14px] uppercase tracking-widest font-bold">{{ $companyName }}</p>
                <h1 class="text-white text-lg font-bold">Member Directory</h1>
            </div>
            <div class="size-10 flex items-center justify-center">
{{--                <span class="text-white/30 text-xs">{{ $total }}</span>--}}
            </div>
        </div>

        {{-- Search --}}
        <div class="px-4 pb-3">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-2.5 text-white/30 pointer-events-none">search</span>
                <input x-model="search" x-on:input="resetPage()" type="text" placeholder="Search by name or ID..."
                       autocomplete="off"
                       class="w-full bg-white/10 border border-white/10 rounded-full py-2.5 pl-12 pr-10 text-white text-sm focus:outline-none focus:ring-1 focus:ring-primary placeholder:text-white/30">
                <button x-show="search" x-on:click="search=''; resetPage()" class="absolute right-4 top-2.5 text-white/40">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>
        </div>


    </header>

    {{-- Stats --}}
    <div class="px-4 py-3">
        <p class="text-white/40 text-sm">
            Showing <span class="text-primary font-bold" x-text="paginated.length"></span>
{{--            of <span class="text-white/60 font-bold" x-text="filtered.length"></span> members--}}
        </p>
    </div>

    {{-- List --}}
    <main class="flex-1 px-4 space-y-3">
        <template x-for="m in paginated" :key="m.id">
            <div class="relative overflow-hidden rounded-xl"
                 style="touch-action: pan-y;">
                <div class="flex items-center gap-4 bg-white/5 border border-white/10 rounded-xl p-3 transition-transform duration-200"
                     x-bind:style="swipeStyles(m.id)"
                     x-on:pointerdown="startSwipe($event, m)"
                     x-on:pointermove="moveSwipe($event)"
                     x-on:pointerup="endSwipe($event, m)"
                     x-on:pointercancel="cancelSwipe()"
                     x-on:pointerleave="endSwipe($event, m)">
                <button type="button"
                        x-on:pointerdown.stop
                        x-on:click.stop="openPreview(m)"
                        class="relative shrink-0 size-12 rounded-full overflow-hidden border border-white/10 bg-primary/10 flex items-center justify-center"
                        :class="m.has_photo ? 'active:scale-95 transition-transform' : 'cursor-default'"
                        :aria-label="'Preview ' + m.name + ' profile picture'">
                    <img :src="m.has_photo ? m.photo_url : null" class="size-full object-cover"
                         x-show="m.has_photo"
                         x-bind:alt="m.name + ' profile picture'">
                    <span class="text-primary font-bold text-sm" x-show="!m.has_photo" x-text="m.initials"></span>
                </button>

                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <a :href="'/directory/' + m.id"
                           x-on:click="cancelClickAfterSwipe($event)"
                           class="block min-w-0">
                            <p class="text-white font-bold text-sm truncate uppercase" x-text="m.name"></p>
                        </a>
                        <p class="text-white/40 text-[10px]" x-text="'Member ID: ' + m.id"></p>
                        <p class="text-primary/50 text-[10px]" x-show="m.category" x-text="m.category"></p>
                    </div>

                    <a :href="'/directory/' + m.id"
                       x-on:click="cancelClickAfterSwipe($event)"
                       class="flex shrink-0 items-center justify-center rounded-full p-1 text-white/20 active:scale-95 transition-transform"
                       :aria-label="'Open ' + m.name + ' profile'">
                        <span class="material-symbols-outlined">chevron_right</span>
                    </a>
                </div>
            </div>
            </div>
        </template>

        <div x-show="filtered.length === 0" class="flex flex-col items-center py-16">
            <span class="material-symbols-outlined text-5xl text-white/20 mb-3">person_search</span>
            <p class="text-white/40 text-sm">No members found</p>
            <button x-on:click="search=''; setFilter('All')" class="mt-3 text-primary text-sm font-bold">Clear filters</button>
        </div>
    </main>

    {{-- Load more --}}
    <div class="py-8 flex justify-center">
        <button x-show="hasMore" x-on:click="page++"
            class="flex items-center gap-2 bg-primary/10 border border-primary/20 text-primary rounded-full px-8 py-2.5 text-sm font-bold active:scale-95 transition-transform">
            <span class="material-symbols-outlined text-base">expand_more</span>
            Load more
        </button>
        <p x-show="!hasMore && filtered.length > 0" class="text-white/20 text-xs self-center"
           x-text="'All ' + filtered.length + ' members loaded'"></p>
    </div>

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
                    <img x-bind:src="previewMember ? previewMember.photo_url : null"
                         x-bind:alt="previewMember ? previewMember.name + ' full-size profile picture' : 'Profile picture preview'"
                         class="size-full object-cover object-top">
                </div>

                <div class="pt-4 text-center">
                    <p class="text-white text-base font-bold" x-text="previewMember ? previewMember.name : ''"></p>
                    <p class="mt-1 text-xs text-white/40" x-text="previewMember ? 'Member ID: ' + previewMember.id : ''"></p>
                </div>
            </div>
        </div>
    </div>

    <div x-show="toast.show"
         x-transition.opacity.duration.200ms
         class="fixed inset-x-4 bottom-24 z-[90] flex justify-center"
         style="display: none;">
        <div class="max-w-sm rounded-full border border-white/10 bg-slate-950/90 px-4 py-3 text-sm font-medium text-white shadow-2xl">
            <span x-text="toast.message"></span>
        </div>
    </div>

    @include('layouts.bottom-nav')

</div>

<script>
(function() {
    var _data = {!! $membersJson !!};

    window.memberDirectory = function() {
        return {
            search: '',
            activeFilter: 'All',
            page: 1,
            perPage: 20,
            previewOpen: false,
            previewMember: null,
            swipe: {
                active: false,
                memberId: null,
                startX: 0,
                currentX: 0,
                offsetX: 0,
                targetEl: null
            },
            swipeOffsets: {},
            suppressClickUntil: 0,
            toast: {
                show: false,
                message: ''
            },
            toastTimer: null,
            all: _data,
            categories: (function() {
                var seen = {}, cats = [];
                for (var i = 0; i < _data.length; i++) {
                    var c = _data[i].category;
                    if (c && !seen[c]) { seen[c] = true; cats.push(c); }
                }
                return cats.sort();
            })(),

            get filtered() {
                var list = this.all;
                var f = this.activeFilter;
                if (f !== 'All') {
                    list = list.filter(function(m) { return m.category === f; });
                }
                var q = this.normalizeSearch(this.search);
                if (q) {
                    var rawQuery = this.search.trim().toLowerCase();

                    list = list.filter((m) => {
                        var normalizedName = this.normalizeSearch(m.name);
                        var normalizedId = this.normalizeSearch(m.id);
                        var rawName = (m.name || '').toLowerCase();
                        var rawId = (m.id || '').toLowerCase();

                        return normalizedName.indexOf(q) !== -1
                            || normalizedId.indexOf(q) !== -1
                            || rawName.indexOf(rawQuery) !== -1
                            || rawId.indexOf(rawQuery) !== -1;
                    });
                }
                return list;
            },

            normalizeSearch(value) {
                return String(value || '')
                    .toLowerCase()
                    .replace(/[\s-]+/g, '');
            },

            get paginated() {
                return this.filtered.slice(0, this.page * this.perPage);
            },

            get hasMore() {
                return this.paginated.length < this.filtered.length;
            },

            swipeStyles: function(memberId) {
                var offset = this.swipeOffsets[memberId] || 0;
                var background = 'transparent';

                if (offset > 16) {
                    background = 'linear-gradient(90deg, rgba(242,208,13,0.16), rgba(242,208,13,0.03))';
                } else if (offset < -16) {
                    background = 'linear-gradient(270deg, rgba(242,208,13,0.16), rgba(242,208,13,0.03))';
                }

                return 'transform: translateX(' + offset + 'px); background: ' + background + ';';
            },

            startSwipe: function(event, member) {
                if (!event.isPrimary) {
                    return;
                }

                this.swipe.active = true;
                this.swipe.memberId = member.id;
                this.swipe.startX = event.clientX;
                this.swipe.currentX = event.clientX;
                this.swipe.offsetX = this.swipeOffsets[member.id] || 0;
                this.swipe.targetEl = event.currentTarget;
                this.swipe.targetEl.style.transition = 'none';
            },

            moveSwipe: function(event) {
                if (!this.swipe.active || this.swipe.memberId === null) {
                    return;
                }

                this.swipe.currentX = event.clientX;

                var deltaX = this.swipe.currentX - this.swipe.startX;
                var limited = Math.max(-112, Math.min(112, deltaX));

                if (Math.abs(limited) > 8 && event.cancelable) {
                    event.preventDefault();
                }

                this.swipe.offsetX = limited;
                this.swipeOffsets = Object.assign({}, this.swipeOffsets, {
                    [this.swipe.memberId]: limited
                });
            },

            endSwipe: function(event, member) {
                if (!this.swipe.active || this.swipe.memberId !== member.id) {
                    return;
                }

                if (event && typeof event.clientX === 'number') {
                    this.swipe.currentX = event.clientX;
                }

                var deltaX = this.swipe.currentX - this.swipe.startX;
                var threshold = 72;

                if (Math.abs(deltaX) >= threshold) {
                    this.suppressClickUntil = Date.now() + 350;

                    if (deltaX < 0) {
                        this.handleCallAction(member);
                    } else {
                        this.handleSmsAction(member);
                    }
                }

                this.resetSwipe(member.id);
            },

            cancelSwipe: function() {
                if (!this.swipe.active || this.swipe.memberId === null) {
                    return;
                }

                this.resetSwipe(this.swipe.memberId);
            },

            resetSwipe: function(memberId) {
                if (this.swipe.targetEl) {
                    this.swipe.targetEl.style.transition = 'transform 200ms ease';
                }

                this.swipeOffsets = Object.assign({}, this.swipeOffsets, {
                    [memberId]: 0
                });

                this.swipe = {
                    active: false,
                    memberId: null,
                    startX: 0,
                    currentX: 0,
                    offsetX: 0,
                    targetEl: null
                };
            },

            cancelClickAfterSwipe: function(event) {
                if (Date.now() < this.suppressClickUntil) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            },

            isMobileDevice: function() {
                return window.matchMedia('(pointer: coarse)').matches
                    || /Android|iPhone|iPad|iPod|IEMobile|Opera Mini/i.test(navigator.userAgent || '');
            },

            showToast: function(message) {
                this.toast.message = message;
                this.toast.show = true;

                if (this.toastTimer) {
                    clearTimeout(this.toastTimer);
                }

                this.toastTimer = setTimeout(() => {
                    this.toast.show = false;
                }, 2200);
            },

            handleEmailAction: function(member) {
                var email = (member.email || '').trim();

                if (!email) {
                    this.showToast('No email found for this member.');
                    return;
                }

                if (this.isMobileDevice()) {
                    this.openMobileGmail(email);
                    return;
                }

                window.open(
                    'https://mail.google.com/mail/?view=cm&fs=1&tf=1&to=' + encodeURIComponent(email),
                    '_blank',
                    'noopener'
                );
                this.showToast('Opened Gmail compose for ' + email + '.');
            },

            openMobileGmail: function(email) {
                var fallback = function() {
                    window.location.href = 'mailto:' + email;
                };

                var ua = navigator.userAgent || '';

                if (/iPhone|iPad|iPod/i.test(ua)) {
                    window.location.href = 'googlegmail://co?to=' + encodeURIComponent(email);
                    setTimeout(fallback, 700);
                } else if (/Android/i.test(ua)) {
                    window.location.href = 'intent://co?to=' + encodeURIComponent(email) + '#Intent;scheme=googlegmail;package=com.google.android.gm;end';
                    setTimeout(fallback, 700);
                } else {
                    fallback();
                }

                this.showToast('Trying to open Gmail for ' + email + '.');
            },

            handleCallAction: function(member) {
                var mobile = (member.mobile || '').trim();

                if (!mobile) {
                    this.showToast('No mobile number found for this member.');
                    return;
                }

                window.location.href = 'tel:' + mobile.replace(/\s+/g, '');
                this.showToast('Calling ' + mobile + '.');
            },

            handleSmsAction: function(member) {
                var mobile = (member.mobile || '').trim();

                if (!mobile) {
                    this.showToast('No mobile number found for this member.');
                    return;
                }

                window.location.href = 'sms:' + mobile.replace(/\s+/g, '');
                this.showToast('Opening SMS for ' + mobile + '.');
            },

            openPreview: function(member) {
                if (!member.has_photo) {
                    return;
                }

                this.previewMember = member;
                this.previewOpen = true;
            },

            closePreview: function() {
                this.previewOpen = false;
                this.previewMember = null;
            },

            setFilter: function(f) { this.activeFilter = f; this.page = 1; },
            resetPage: function()  { this.page = 1; }
        };
    };
})();
</script>

@endsection
