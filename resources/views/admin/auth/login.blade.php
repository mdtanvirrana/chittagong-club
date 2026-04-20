<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - {{ $companyName }}</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        admin: {
                            ink: '#07131d',
                            gold: '#f0c441',
                            teal: '#64d2c7',
                        },
                    },
                    fontFamily: {
                        display: ['Space Grotesk', 'sans-serif'],
                        body: ['Instrument Sans', 'sans-serif'],
                    },
                },
            },
        };
    </script>

    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(100, 210, 199, 0.12), transparent 32%),
                radial-gradient(circle at bottom right, rgba(240, 196, 65, 0.10), transparent 28%),
                linear-gradient(135deg, #07131d 0%, #0f2436 100%);
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 antialiased">
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
