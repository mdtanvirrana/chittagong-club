<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - {{ $companyName }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @php
        $adminTheme = config('theme.admin', []);
        $adminDisplayFont = data_get($adminTheme, 'fonts.display.family', 'Space Grotesk');
        $adminDisplayWeights = data_get($adminTheme, 'fonts.display.weights', '500;700');
        $adminBodyFont = data_get($adminTheme, 'fonts.body.family', 'Instrument Sans');
        $adminBodyWeights = data_get($adminTheme, 'fonts.body.weights', '400;500;600;700');
        $adminDisplayFontUrl = str_replace(' ', '+', $adminDisplayFont) . ':wght@' . $adminDisplayWeights;
        $adminBodyFontUrl = str_replace(' ', '+', $adminBodyFont) . ':wght@' . $adminBodyWeights;
        $hexToRgb = static function ($hex, $fallback) {
            $hex = ltrim(trim((string) $hex), '#');

            if (strlen($hex) === 3) {
                $hex = preg_replace('/(.)/', '$1$1', $hex);
            }

            if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
                return $fallback;
            }

            return implode(', ', [
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2)),
            ]);
        };
        $adminText = data_get($adminTheme, 'colors.text', '#111827');
        $adminTextSoft = data_get($adminTheme, 'colors.text_soft', '#475569');
        $adminTextMuted = data_get($adminTheme, 'colors.text_muted', '#64748b');
        $adminTextRgb = $hexToRgb($adminText, '17, 24, 39');
        $adminTextSoftRgb = $hexToRgb($adminTextSoft, '71, 85, 105');
        $adminTextMutedRgb = $hexToRgb($adminTextMuted, '100, 116, 139');
    @endphp

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ $adminBodyFontUrl }}&family={{ $adminDisplayFontUrl }}&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        admin: {
                            ink: @js(data_get($adminTheme, 'colors.ink', '#0b1220')),
                            gold: @js(data_get($adminTheme, 'colors.accent', '#d9b24c')),
                            teal: @js(data_get($adminTheme, 'colors.login_support', '#64d2c7')),
                        },
                    },
                    fontFamily: {
                        display: [@js($adminDisplayFont), 'sans-serif'],
                        body: [@js($adminBodyFont), 'sans-serif'],
                    },
                },
            },
        };
    </script>

    <style>
        :root {
            --admin-text: {{ $adminText }};
            --admin-text-rgb: {{ $adminTextRgb }};
            --admin-text-soft-rgb: {{ $adminTextSoftRgb }};
            --admin-text-muted-rgb: {{ $adminTextMutedRgb }};
        }

        body {
            font-family: '{{ $adminBodyFont }}', sans-serif;
            background:
                radial-gradient(circle at top left, {{ data_get($adminTheme, 'colors.login_glow_primary', 'rgba(100, 210, 199, 0.12)') }}, transparent 32%),
                radial-gradient(circle at bottom right, {{ data_get($adminTheme, 'colors.login_glow_accent', 'rgba(240, 196, 65, 0.10)') }}, transparent 28%),
                linear-gradient(135deg, {{ data_get($adminTheme, 'colors.login_background_start', '#07131d') }} 0%, {{ data_get($adminTheme, 'colors.login_background_end', '#0f2436') }} 100%);
            color: var(--admin-text);
        }

        input::placeholder,
        textarea::placeholder {
            color: #94a3b8 !important;
            opacity: 1;
        }

        [class~="bg-slate-950/45"] {
            background-color: rgba(255, 255, 255, 0.97) !important;
        }

        [class~="text-white"] {
            color: var(--admin-text) !important;
        }

        [class~="text-white/80"] {
            color: rgba(var(--admin-text-soft-rgb), 0.92) !important;
        }

        [class~="text-white/45"],
        [class~="text-white/25"] {
            color: rgba(var(--admin-text-muted-rgb), 0.88) !important;
        }
    </style>
</head>
<body class="min-h-screen text-slate-900 antialiased">
<div
    x-data="{ showPassword: false, loading: false }"
    class="mx-auto grid min-h-screen max-w-7xl items-center gap-8 px-4 py-6 lg:grid-cols-[1.15fr_minmax(420px,520px)] lg:px-8"
>
    <section class="rounded-[2rem] border border-white/10 bg-slate-950/45 p-6 shadow-2xl backdrop-blur-xl sm:p-8">
        <div class="mb-8">
            <p class="text-xs uppercase tracking-[0.24em] text-admin-gold">Admin Access</p>
            <h2 class="mt-3 font-display text-3xl font-bold text-white">Sign in</h2>
               </div>

        <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-5" @submit="loading = true">
            @csrf

            @if (session('session_expired'))
                <div class="rounded-2xl border border-amber-400/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                    {{ session('session_expired') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-100">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label for="login" class="mb-2 block text-sm font-medium text-white/80">Admin user ID or username</label>
                <input
                    id="login"
                    name="login"
                    type="text"
                    value="{{ old('login') }}"
                    autocomplete="username"
                    class="w-full rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3 text-white placeholder:text-white/25 focus:border-admin-gold/40 focus:ring-admin-gold/25"
                    placeholder="e.g. 10001 or system.user"
                >
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-medium text-white/80">Password</label>
                <div class="relative">
                    <input
                        id="password"
                        name="password"
                        :type="showPassword ? 'text' : 'password'"
                        autocomplete="current-password"
                        class="w-full rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3 pr-14 text-white placeholder:text-white/25 focus:border-admin-gold/40 focus:ring-admin-gold/25"
                        placeholder="Enter password"
                    >
                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center px-4 text-sm text-white/45">
                        <span x-text="showPassword ? 'Hide' : 'Show'"></span>
                    </button>
                </div>
            </div>

            <button
                type="submit"
                :disabled="loading"
                class="flex w-full items-center justify-center gap-3 rounded-2xl bg-admin-gold px-4 py-3 font-display text-base font-bold text-admin-ink transition-transform hover:scale-[0.99] disabled:opacity-70"
            >
                <span x-show="!loading">Enter Admin Panel</span>
                <span x-show="loading">Signing in...</span>
            </button>
        </form>
    </section>
</div>
</body>
</html>
