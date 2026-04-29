@php
    use Illuminate\Support\HtmlString;$sidebarSections = [
        [
            'heading' => 'Club Info',
            'items' => [
                ['label' => 'About CCL', 'icon' => 'info', 'route' => 'about', 'match' => 'about'],
                ['label' => 'Affiliated Clubs', 'icon' => 'handshake', 'route' => 'affiliated-clubs', 'match' => 'affiliated-clubs'],
                ['label' => 'Contact Information', 'icon' => 'contacts', 'route' => 'contact', 'match' => 'contact'],
                ['label' => 'Dress Code', 'icon' => 'checkroom', 'route' => 'dress-code', 'match' => 'dress-code'],
                ['label' => 'General Rules', 'icon' => 'gavel', 'route' => 'general-rules', 'match' => 'general-rules'],
            ],
        ],
        [
            'heading' => 'Services',
            'items' => [
                ['label' => 'Facilities', 'icon' => 'apartment', 'route' => 'facilities', 'match' => 'facilities'],
                ['label' => 'Gallery', 'icon' => 'photo_library', 'route' => 'gallery', 'match' => 'gallery'],
                ['label' => 'Greetings Calendar', 'icon' => 'calendar_month', 'route' => null],
            ],
        ],
        [
            'heading' => 'Members',
            'items' => [
                [
                    'label' => 'General Committee',
                    'label_html' =>  'General Committee',
                    'icon' => 'groups',
                    'route' => 'executive',
                    'match' => 'executive',
                ],
                ['label' => 'Former Chairmen', 'icon' => 'history_edu', 'route' => 'former-chairman', 'match' => 'former-chairman'],
                ['label' => 'Employee Directory', 'icon' => 'badge', 'route' => 'employee-directory', 'match' => 'employee-directory'],
            ],
        ],
        [
            'heading' => 'Legal',
            'items' => [
                ['label' => 'Terms & Conditions', 'icon' => 'gavel', 'route' => 'legal.terms', 'match' => 'legal.terms'],
                ['label' => 'Privacy Policy', 'icon' => 'shield', 'route' => 'legal.privacy', 'match' => 'legal.privacy'],
                ['label' => 'Return and Refund', 'icon' => 'payments', 'route' => 'legal.refund', 'match' => 'legal.refund'],
                ['label' => 'Data Policy', 'icon' => 'database', 'route' => 'legal.data', 'match' => 'legal.data'],
                ['label' => 'Contact Us', 'icon' => 'contact_support', 'route' => 'legal.contact', 'match' => 'legal.contact'],
            ],
        ],
    ];
@endphp

<div
    x-show="sidebarOpen"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="sidebarOpen = false"
    class="fixed inset-0 z-[60] bg-black/60 backdrop-blur-sm"
    style="display: none;"
></div>

<aside
    x-show="sidebarOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="member-sidebar fixed top-0 left-0 z-[70] h-full w-72 bg-white border-r border-primary/10 flex flex-col overflow-hidden"
    style="display: none;"
>
    <div class="px-5 pt-5 pb-5 border-b border-primary/10 bg-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('logo.png') }}" alt="CCL" class="size-9 rounded-full object-contain"/>
                <div>
                    <p class="company-name-canterbury text-slate-900 text-lg leading-tight">{{ $companyName }}</p>
                    <p class="text-primary text-[10px] font-bold uppercase tracking-wider">Est. 1878</p>
                </div>
            </div>
            <button @click="sidebarOpen = false"
                    class="size-8 flex items-center justify-center rounded-full bg-primary/10 text-primary"
                    aria-label="Close sidebar">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 hide-scrollbar">
        @foreach ($sidebarSections as $section)
            <div class="mb-2">
                <p class="px-5 py-2 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">
                    {{ $section['heading'] }}
                </p>

                @foreach ($section['items'] as $item)
                    @if ($item['route'])
                        @php
                            $isActive = request()->routeIs($item['match'] ?? $item['route']);
                        @endphp
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 px-5 py-3 transition-colors {{ $isActive ? 'bg-primary/10 text-primary' : 'text-primary hover:bg-primary/10' }}">
                            <span
                                class="material-symbols-outlined text-xl shrink-0 {{ $isActive ? 'text-primary' : 'text-primary' }}">{{ $item['icon'] }}</span>
                            <span class="text-sm font-medium">{!! $item['label_html'] ?? e($item['label']) !!}</span>
                        </a>
                    @else
                        <div class="flex items-center gap-3 px-5 py-3 text-slate-400 cursor-not-allowed">
                            <span
                                class="material-symbols-outlined text-slate-400 text-xl shrink-0">{{ $item['icon'] }}</span>
                            <span class="text-sm font-medium">{{ $item['label'] }}</span>
                            <span
                                class="ml-auto rounded-full bg-primary/10 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-primary">Soon</span>
                        </div>
                    @endif
                @endforeach
            </div>
        @endforeach
    </nav>

    <div class="px-5 py-4 border-t border-primary/10 shrink-0">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full flex items-center gap-3 py-3 px-4 rounded-xl bg-primary/10 border border-primary/20 text-primary transition-colors">
                <span class="material-symbols-outlined text-lg">logout</span>
                <span class="text-sm font-bold">Sign Out</span>
            </button>
        </form>
    </div>
</aside>
