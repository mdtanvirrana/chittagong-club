@extends('layouts.app')
@section('page_title', 'Set New Password')

@push('styles')
    @include('partials.member-auth-styles')
@endpush

@section('content')
<div
    x-data="{ loading: false, showPassword: false, showConfirmPassword: false }"
    class="relative flex min-h-screen w-full flex-col overflow-hidden blue-depth-gradient"
>
    <div class="h-12 w-full"></div>

    @include('partials.member-auth-hero', [
        'eyebrow' => 'Registration',
        'stepLabel' => 'Step 3 of 3',
        'sectionTitle' => 'Create Your Password',
        'sectionDescription' => 'OTP confirmed. Set your password now, then sign in from the login page.',
    ])

    <div class="flex flex-1 flex-col justify-start px-8 pb-12">
        <form action="{{ route('password.initial.store') }}" method="POST" class="space-y-6" @submit="loading = true">
            @csrf

            @if (session('password_setup_status'))
                <div class="flex items-center gap-3 rounded-xl border border-amber-400/30 bg-amber-500/15 px-4 py-3">
                    <span class="material-symbols-outlined shrink-0 text-amber-300">lock_reset</span>
                    <p class="text-sm text-amber-700">{{ session('password_setup_status') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="flex items-center gap-3 rounded-xl border border-red-500/30 bg-red-500/15 px-4 py-3">
                    <span class="material-symbols-outlined shrink-0 text-red-400">error</span>
                    <p class="text-sm text-red-700">{{ $errors->first() }}</p>
                </div>
            @endif

            <div class="auth-floating-card rounded-[1.75rem] px-5 py-5">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-primary/70">Member ID</p>
                <p class="mt-3 text-lg font-bold text-black">{{ data_get($setupState, 'member_id') }}</p>
                <p class="mt-2 text-sm leading-6 text-black">{{ data_get($setupState, 'member_name', 'Member') }}</p>
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-semibold uppercase tracking-widest text-primary/80">New Password</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                        <span class="material-symbols-outlined login-icon">lock</span>
                    </div>
                    <input
                        name="password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="••••••••"
                        autocomplete="new-password"
                        class="login-input block w-full rounded-xl border border-primary/10 py-4 pl-12 pr-12 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all {{ $errors->has('password') ? 'border-red-500/50' : 'border-primary/10' }}"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="login-toggle absolute inset-y-0 right-0 flex items-center pr-4"
                    >
                        <span class="material-symbols-outlined"
                              :class="{ 'is-active': showPassword }"
                              x-text="showPassword ? 'visibility' : 'visibility_off'">
                        </span>
                    </button>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-semibold uppercase tracking-widest text-primary/80">Confirm Password</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                        <span class="material-symbols-outlined login-icon">verified_user</span>
                    </div>
                    <input
                        name="password_confirmation"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        placeholder="••••••••"
                        autocomplete="new-password"
                        class="login-input block w-full rounded-xl border border-primary/10 py-4 pl-12 pr-12 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all"
                    />
                    <button
                        type="button"
                        @click="showConfirmPassword = !showConfirmPassword"
                        class="login-toggle absolute inset-y-0 right-0 flex items-center pr-4"
                    >
                        <span class="material-symbols-outlined"
                              :class="{ 'is-active': showConfirmPassword }"
                              x-text="showConfirmPassword ? 'visibility' : 'visibility_off'">
                        </span>
                    </button>
                </div>
            </div>

            <button
                type="submit"
                :disabled="loading"
                class="flex w-full items-center justify-center gap-2 rounded-xl py-4 font-bold text-brand-blue gold-btn-gradient shadow-[0_10px_20px_-5px_rgba(242,204,13,0.3)] transition-all active:scale-[0.98] disabled:opacity-70"
            >
                <span x-show="!loading">SAVE PASSWORD</span>
                <span x-show="loading" class="flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    Saving…
                </span>
                <span class="material-symbols-outlined text-xl" x-show="!loading">lock_reset</span>
            </button>
        </form>
    </div>

    <div class="flex h-8 w-full items-end justify-center pb-2">
        <div class="h-1 w-32 rounded-full bg-white/20"></div>
    </div>
</div>
@endsection
