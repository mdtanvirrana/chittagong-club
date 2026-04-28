<header class="sticky top-0 z-50 flex items-center justify-between px-4 py-4 bg-brand-blue/90 ios-blur">
    <div class="flex items-center gap-3">
        <button @click="sidebarOpen = true"
                class="size-10 flex items-center justify-center rounded-full hover:bg-white/10 transition-colors"
                aria-label="Open sidebar">
            <span class="material-symbols-outlined text-white">menu</span>
        </button>
        <img class="w-8 h-8 object-contain rounded-full"
             src="{{ asset('logo.png') }}"
             alt="Chittagong Club Logo" />
        <h1 class="company-name-canterbury text-white text-2xl leading-tight">{{ $companyName }}</h1>
    </div>

    <div class="flex gap-1">
        @hasSection('userpanel_header_actions')
            @yield('userpanel_header_actions')
        @else
            <a href="{{ route('notice-board') }}"
               class="relative p-2 rounded-full hover:bg-white/10 transition-colors"
               aria-label="Open notice board">
                <span class="material-symbols-outlined text-primary">notifications</span>
                <span class="absolute top-2 right-2 flex h-2 w-2 rounded-full bg-red-500 border border-brand-blue"></span>
            </a>
        @endif
    </div>
</header>
