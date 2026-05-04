@extends('layouts.app')
@section('page_title', 'Login')

@push('styles')
    @include('partials.member-auth-styles')
@endpush

@section('content')
    <div
        x-data="{
        showPassword: false,
        loading: false,
        memberId: '',
        password: '',
    }"
        class="relative flex min-h-screen w-full flex-col blue-depth-gradient overflow-x-hidden"
    >
        {{-- Status bar spacer --}}
        <div class="h-3 w-full"></div>


        @include('partials.member-auth-hero')

        {{-- Login Form --}}
        <div class="flex-1 px-8 pb-3 flex flex-col justify-start w-full">
            <form action="{{ route('login.post') }}" method="POST" class="space-y-3" @submit="loading = true">
                @csrf

                @if (session('password_reset_status'))
                    <div
                        class="flex items-center gap-3 bg-emerald-500/15 border border-emerald-400/30 rounded-xl px-4 py-3">
                        <span class="material-symbols-outlined text-emerald-300 shrink-0">check_circle</span>
                        <p class="text-emerald-700 text-sm">{{ session('password_reset_status') }}</p>
                    </div>
                @endif

                @if (session('session_expired'))
                    <div
                        class="flex items-center gap-3 bg-amber-500/15 border border-amber-400/30 rounded-xl px-4 py-3">
                        <span class="material-symbols-outlined text-amber-300 shrink-0">schedule</span>
                        <p class="text-amber-700 text-sm">{{ session('session_expired') }}</p>
                    </div>
                @endif

                {{-- Error alert --}}
                @if ($errors->any())
                    <div class="flex items-center gap-3 bg-red-500/15 border border-red-500/30 rounded-xl px-4 py-3">
                        <span class="material-symbols-outlined text-red-400 shrink-0">error</span>
                        <p class="text-red-700 text-sm">{{ $errors->first() }}</p>
                    </div>
                @endif

                {{-- Membership ID --}}
                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-primary/80 uppercase tracking-widest ml-1">
                        Membership ID
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined login-icon">badge</span>
                        </div>
                        <input
                            name="member_id"
                            type="text"
                            value="{{ old('member_id') }}"
                            placeholder="e.g. CCL-88291"
                            autocomplete="username"
                            class="login-input block w-full {{ $errors->has('member_id') ? 'border-red-500/50' : 'border-primary/10' }} rounded-xl py-4 pl-12 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all"
                        />
                    </div>
                </div>

                {{-- Password --}}
                <div class="space-y-2">
                    <label
                        class="block text-xs font-semibold text-primary/80 uppercase tracking-widest">Password</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined login-icon">lock</span>
                        </div>
                        <input
                            name="password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            class="login-input block w-full border border-primary/10 rounded-xl py-4 pl-12 pr-12 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="login-toggle absolute inset-y-0 right-0 pr-4 flex items-center"
                        >
                        <span class="material-symbols-outlined"
                              :class="{ 'is-active': showPassword }"
                              x-text="showPassword ? 'visibility' : 'visibility_off'">
                        </span>
                        </button>
                    </div>
                    <div class="flex justify-end">
                        <a
                            href="{{ route('password.forgot') }}"
                            class="text-xs font-semibold uppercase tracking-[0.2em] text-primary/80 transition hover:text-primary"
                        >
                            Forgot Password?
                        </a>
                    </div>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full gold-btn-gradient text-brand-blue font-bold py-4 rounded-xl shadow-[0_10px_20px_-5px_rgba(242,204,13,0.3)] active:scale-[0.98] transition-all flex items-center justify-center gap-2 disabled:opacity-70"
                >
                    <span x-show="!loading">SIGN IN TO PORTAL</span>
                    <span x-show="loading" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    Signing in…
                </span>
                    <span class="material-symbols-outlined text-xl" x-show="!loading">login</span>
                </button>

            </form>

            <div class="mt-5">
                <a
                    href="{{ route('password.initial.create') }}"
                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-primary/15 bg-white/70 px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-primary shadow-[0_14px_32px_-28px_rgba(185,28,28,0.35)] transition hover:bg-white/90"
                >
                    <span class="material-symbols-outlined text-base">lock_reset</span>
                    First Login Password Set
                </a>
            </div>

            <section class="mt-6 border-t border-primary/10 pt-4">
                <p class="text-center text-[10px] font-bold uppercase tracking-[0.25em] text-primary/70">Club Policies</p>
                <div class="mt-5 flex flex-col items-center space-y-3 text-sm font-medium">
                    <a href="{{ route('legal.terms') }}" class="text-gray-600 hover:text-primary hover:underline transition">Terms &amp; Conditions</a>
                    <a href="{{ route('legal.refund') }}" class="text-gray-600 hover:text-primary hover:underline transition">Return and Refund Policy</a>
                    <a href="{{ route('legal.privacy') }}" class="text-gray-600 hover:text-primary hover:underline transition">Privacy Policy</a>
                    <a href="{{ route('legal.data') }}" class="text-gray-600 hover:text-primary hover:underline transition">Data Policy</a>
                    <a href="{{ route('legal.contact') }}" class="text-gray-600 hover:text-primary hover:underline transition">Contact Us</a>
                </div>
            </section>
        </div>

        {{-- iOS home indicator --}}
        <div class="h-8 w-full flex justify-center items-end pb-2">
            <div class="w-32 h-1 bg-white/20 rounded-full"></div>
        </div>
    </div>
@endsection
