{{-- Bottom Navigation Bar — shared across all inner pages --}}
<nav
    x-data="{ current: '{{ Route::currentRouteName() }}' }"
    class="fixed bottom-0 inset-x-0 z-50 flex justify-center"
>
    <div class="w-full max-w-[425px] h-20 bg-background-dark/90 ios-blur border-t border-white/10 flex items-center justify-around px-4">

        {{-- Home --}}
        <a href="{{ route('dashboard') }}"
           class="flex flex-col items-center gap-1 transition-colors {{ Route::currentRouteName() === 'dashboard' ? 'text-club-gold' : 'text-white/40' }}">
            <span class="material-symbols-outlined">home</span>
            <span class="text-[10px] font-bold uppercase tracking-wider">Home</span>
        </a>

        {{-- Members --}}
        <a href="{{ route('directory') }}"
           class="flex flex-col items-center gap-1 transition-colors {{ Route::currentRouteName() === 'directory' ? 'text-club-gold' : 'text-white/40' }}">
            <span class="material-symbols-outlined">group</span>
            <span class="text-[10px] font-bold uppercase tracking-wider">Members</span>
        </a>

        {{-- Notice Board --}}
        <a href="{{ route('notice-board') }}"
           class="flex flex-col items-center gap-1 transition-colors {{ Route::currentRouteName() === 'notice-board' ? 'text-club-gold' : 'text-white/40' }}">
            <span class="material-symbols-outlined">campaign</span>
            <span class="text-[10px] font-bold uppercase tracking-wider">Notices</span>
        </a>

        {{-- Shop --}}
        <a href="{{ route('shop') }}"
           class="flex flex-col items-center gap-1 transition-colors {{ Route::currentRouteName() === 'shop' ? 'text-club-gold' : 'text-white/40' }}">
            <span class="material-symbols-outlined">shopping_bag</span>
            <span class="text-[10px] font-bold uppercase tracking-wider">Shop</span>
        </a>

        {{-- Profile --}}
        <a href="{{ route('profile') }}"
           class="flex flex-col items-center gap-1 transition-colors {{ Route::currentRouteName() === 'profile' ? 'text-club-gold' : 'text-white/40' }}">
            <span class="material-symbols-outlined">person</span>
            <span class="text-[10px] font-bold uppercase tracking-wider">Profile</span>
        </a>

    </div>
</nav>
