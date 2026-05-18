<header class="sticky top-0 z-50 flex items-center justify-between bg-brand-blue/90 px-4 py-4 ios-blur">
    <div class="flex min-w-0 items-center gap-3">
        <button
            @click="sidebarOpen = true"
            class="flex size-10 shrink-0 items-center justify-center rounded-full transition-colors hover:bg-white/10"
            aria-label="Open sidebar"
        >
            <span class="material-symbols-outlined text-white">menu</span>
        </button>
        <img
            class="h-8 w-8 shrink-0 rounded-full object-contain"
            src="{{ $companyLogoUrl }}"
            alt="{{ $companyName }} Logo"
        />
        <h1 class="company-name-canterbury truncate text-2xl leading-tight text-white">{{ $companyName }}</h1>
    </div>

    <div class="flex shrink-0 gap-1">
        @hasSection('userpanel_header_actions')
            @yield('userpanel_header_actions')
        @else
            <a
                href="{{ route('notifications.index') }}"
                x-data="memberNotificationBell({
                    indexUrl: @js(route('notifications.index')),
                })"
                x-init="init()"
                @ccl:notification.window="receive($event.detail)"
                class="relative flex size-10 items-center justify-center rounded-full transition-colors hover:bg-white/10"
                aria-label="Open notifications"
            >
                <span class="material-symbols-outlined text-white">notifications</span>
                <span
                    x-show="unreadCount > 0"
                    x-cloak
                    class="absolute right-1 top-1 min-w-5 rounded-full border border-brand-blue bg-red-500 px-1.5 text-center text-[10px] font-black leading-5 text-white"
                    x-text="badgeText()"
                ></span>
            </a>
        @endif
    </div>
</header>
