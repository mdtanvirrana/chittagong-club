<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Chittagong Club Ltd.')</title>

    {{-- Livewire Styles --}}
    @livewireStyles

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    {{-- Alpine.js CDN (for non-Livewire pages) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary:           '#f2d00d',
                        'brand-blue':      '#0c5c8b',
                        'background-dark': '#0c5c8b',
                        'club-navy':       '#0c5c8b',
                        'club-gold':       '#ecc813',
                    },
                    fontFamily: {
                        display: ['Manrope', 'sans-serif'],
                    },
                    borderRadius: {
                        DEFAULT: '0.5rem',
                        lg: '1rem',
                        xl: '1.5rem',
                        full: '9999px',
                    },
                },
            },
        };
    </script>

    <style>
        .mobile-container {
            max-width: 425px;
            margin: 0 auto;
            background: linear-gradient(180deg, #02568a 0%, #0a3d62 100%);
            min-height: 100dvh;
            position: relative;
        }
        body {
            font-family: 'Manrope', sans-serif;
            -webkit-tap-highlight-color: transparent;
            min-height: max(884px, 100dvh);
            background: #0c5c8b;
        }
        .ios-blur {
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .gold-gradient {
            background: linear-gradient(135deg, #f2d00d 0%, #ffdf40 50%, #d4af37 100%);
        }
        .card-glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(236, 200, 19, 0.2);
        }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .gold-border { border: 2px solid #f2d00d; }
    </style>

    @stack('styles')
</head>
<body class="text-white antialiased font-display">

<div class="mobile-container">

    {{-- Blade views use @yield, Livewire full-page uses $slot --}}
    @hasSection('content')
        @yield('content')
    @else
        {{ $slot }}
    @endif

    {{-- Bottom nav: shown for regular Blade pages via @section('show_nav')
         For Livewire pages, include it directly in the component view --}}
    @hasSection('show_nav')
        @include('layouts.bottom-nav')
    @endif

</div>

{{-- Livewire Scripts --}}
@livewireScripts

</body>
</html>
