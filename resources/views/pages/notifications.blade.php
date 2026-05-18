@extends('layouts.userpanel')
@section('page_title', 'Notifications')
@section('show_nav', true)

@section('userpanel_content')
<div
    x-data="notificationPage({
        indexUrl: @js(route('notifications.index')),
        readUrlTemplate: @js(route('notifications.read', ['notification' => '__ID__'])),
        readAllUrl: @js(route('notifications.read-all')),
        csrfToken: @js(csrf_token()),
    })"
    x-init="init()"
    @ccl:notification.window="receive($event.detail)"
    class="flex min-h-screen flex-col pb-24"
>
    <section class="userpanel-subheader sticky top-0 z-40 rounded-b-xl bg-primary/5 p-4 pb-5 shadow-lg">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-black/40">Member Center</p>
                <h2 class="mt-1 text-xl font-black text-black">Notifications</h2>
            </div>
            <button
                type="button"
                @click="markAllRead()"
                x-show="unreadCount > 0"
                class="rounded-full bg-primary px-4 py-2 text-xs font-black uppercase tracking-wider text-white"
            >
                Mark all read
            </button>
        </div>

        <div class="relative mt-4">
            <div class="pointer-events-none absolute inset-y-0 left-4 flex items-center">
                <span class="material-symbols-outlined text-black/35 text-xl">search</span>
            </div>
            <input
                x-model="search"
                type="text"
                placeholder="Search notifications"
                autocomplete="off"
                class="w-full rounded-full border border-primary/10 bg-white/80 py-2.5 pl-11 pr-10 text-sm text-black placeholder:text-black/35 focus:outline-none focus:ring-2 focus:ring-primary/40"
            />
            <button
                type="button"
                x-show="search"
                @click="search = ''"
                class="absolute inset-y-0 right-4 flex items-center text-black/40"
            >
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
    </section>

    <div class="flex items-center justify-between px-4 py-3">
        <p class="text-sm text-black/45">
            <span class="font-black text-primary" x-text="filtered.length"></span>
            notifications
        </p>
        <p class="text-xs text-black/35" x-text="unreadCount > 0 ? unreadCount + ' unread' : 'All caught up'"></p>
    </div>

    <main class="flex-1 space-y-3 px-4">
        <template x-if="loading">
            <div class="space-y-3">
                <div class="h-24 animate-pulse rounded-xl bg-primary/10"></div>
                <div class="h-24 animate-pulse rounded-xl bg-primary/10"></div>
                <div class="h-24 animate-pulse rounded-xl bg-primary/10"></div>
            </div>
        </template>

        <template x-for="notification in filtered" :key="notification.id">
            <button
                type="button"
                @click="openNotification(notification)"
                class="w-full rounded-xl border p-4 text-left transition active:scale-[0.98]"
                :class="notification.read ? 'border-primary/10 bg-white/60' : 'border-primary/20 bg-primary/10'"
            >
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex size-11 shrink-0 items-center justify-center rounded-xl bg-white text-primary shadow-sm">
                        <span class="material-symbols-outlined text-xl" x-text="iconFor(notification)"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start gap-2">
                            <p class="min-w-0 flex-1 text-sm font-black leading-snug text-black" x-text="notification.title"></p>
                            <span x-show="!notification.read" class="mt-1.5 size-2 shrink-0 rounded-full bg-primary"></span>
                        </div>
                        <p class="mt-1 line-clamp-3 text-xs leading-5 text-black/60" x-text="notification.body || 'Open this notification for details.'"></p>
                        <p class="mt-2 text-[10px] font-bold uppercase tracking-[0.18em] text-black/35" x-text="labelFor(notification)"></p>
                    </div>
                    <span class="material-symbols-outlined mt-2 shrink-0 text-lg text-primary/45">chevron_right</span>
                </div>
            </button>
        </template>

        <div x-show="!loading && filtered.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
            <span class="material-symbols-outlined text-5xl text-primary/35">notifications_off</span>
            <p class="mt-3 text-sm font-bold text-black">No notifications found</p>
            <button type="button" x-show="search" @click="search = ''" class="mt-3 text-sm font-black text-primary">Clear search</button>
        </div>
    </main>
</div>

<script>
    window.notificationPage = (config) => ({
        loading: true,
        unreadCount: 0,
        notifications: [],
        search: '',

        init() {
            this.fetchNotifications();
        },

        get filtered() {
            const q = this.search.trim().toLowerCase();

            if (!q) {
                return this.notifications;
            }

            return this.notifications.filter((notification) => (
                String(notification.title || '').toLowerCase().includes(q)
                || String(notification.body || '').toLowerCase().includes(q)
                || String(notification.type || '').toLowerCase().includes(q)
            ));
        },

        iconFor(notification) {
            if (notification.type === 'payment') return 'payments';
            if (notification.type === 'ledger') return 'account_balance_wallet';
            if (notification.type === 'circular') return 'article';
            if (notification.type === 'notice') return 'campaign';

            return 'notifications';
        },

        labelFor(notification) {
            if (notification.type === 'payment') return 'Payment';
            if (notification.type === 'ledger') return 'Ledger reminder';
            if (notification.type === 'circular') return 'Circular';
            if (notification.type === 'notice') return 'Notice';

            return 'Notification';
        },

        receive(notification) {
            if (!notification || !notification.id) {
                return;
            }

            const existing = this.notifications.find((item) => Number(item.id) === Number(notification.id));

            if (existing) {
                Object.assign(existing, notification, { read: existing.read ?? false });
                return;
            }

            this.notifications.unshift(Object.assign({ read: false }, notification));
            this.unreadCount += 1;
        },

        async fetchNotifications() {
            this.loading = true;

            try {
                const response = await fetch(config.indexUrl, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Unable to load notifications.');
                }

                const payload = await response.json();
                this.unreadCount = Number(payload.unread_count || 0);
                this.notifications = Array.isArray(payload.notifications) ? payload.notifications : [];
            } catch (error) {
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        async markRead(notification) {
            if (!notification || notification.read) {
                return;
            }

            notification.read = true;
            this.unreadCount = Math.max(0, this.unreadCount - 1);

            try {
                await fetch(config.readUrlTemplate.replace('__ID__', encodeURIComponent(notification.id)), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
            } catch (error) {
                console.error(error);
            }
        },

        async markAllRead() {
            this.unreadCount = 0;
            this.notifications = this.notifications.map((notification) => Object.assign({}, notification, { read: true }));

            try {
                await fetch(config.readAllUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
            } catch (error) {
                console.error(error);
                this.fetchNotifications();
            }
        },

        async openNotification(notification) {
            await this.markRead(notification);

            if (notification.action_url) {
                window.location.href = notification.action_url;
            }
        },
    });
</script>
@endsection
