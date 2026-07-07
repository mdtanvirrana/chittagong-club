@extends('layouts.app')
@section('page_title', 'OTP Verification')

@push('styles')
    @include('partials.member-auth-styles')
@endpush

@section('content')
<div
    x-data="{ loading: false, resending: false, code: @js(old('code', '')) }"
    class="relative flex min-h-screen w-full flex-col overflow-hidden blue-depth-gradient"
>
    <div class="h-12 w-full"></div>

    @include('partials.member-auth-hero', [
        'eyebrow' => 'Registration',
        'stepLabel' => 'Step 2 of 3',
        'sectionTitle' => 'Confirm OTP',
        'sectionDescription' => 'A 6-digit code was sent to ' . data_get($setupState, 'phone.masked') . '. The OTP stays valid for 5 minutes.',
    ])

    <div class="flex flex-1 flex-col justify-start px-8 pb-12">
        <form action="{{ route('password.initial.verify.store') }}" method="POST" class="space-y-6" @submit="loading = true">
            @csrf

            @if ($errors->any())
                <div class="flex items-center gap-3 rounded-xl border border-red-500/30 bg-red-500/15 px-4 py-3">
                    <span class="material-symbols-outlined shrink-0 text-red-400">error</span>
                    <p class="text-sm text-red-700">{{ $errors->first() }}</p>
                </div>
            @endif

            <div class="space-y-2">
                <label class="ml-1 block text-xs font-semibold uppercase tracking-widest text-primary/80">
                    OTP Code
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                        <span class="material-symbols-outlined login-icon">pin</span>
                    </div>
                    <input
                        name="code"
                        type="text"
                        x-model="code"
                        inputmode="numeric"
                        maxlength="6"
                        autocomplete="one-time-code"
                        placeholder=""
                        class="login-input block w-full rounded-xl py-4 pl-12 pr-4 text-center text-2xl font-bold tracking-[0.45em] focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all {{ $errors->has('code') ? 'border-red-500/50' : 'border-primary/10' }}"
                    />
                </div>
            </div>

            <button
                type="submit"
                :disabled="loading"
                class="flex w-full items-center justify-center gap-2 rounded-xl py-4 font-bold text-brand-blue gold-btn-gradient shadow-[0_10px_20px_-5px_rgba(242,204,13,0.3)] transition-all active:scale-[0.98] disabled:opacity-70"
            >
                <span x-show="!loading">VERIFY OTP</span>
                <span x-show="loading" class="flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    Verifying...
                </span>
                <span class="material-symbols-outlined text-xl" x-show="!loading">verified</span>
            </button>
        </form>

        <form action="{{ route('password.initial.verify.resend') }}" method="POST" class="mt-4" @submit="resending = true">
            @csrf
            <button
                type="submit"
                :disabled="resending"
                class="flex w-full items-center justify-center gap-2 rounded-xl border border-primary/15 bg-white/65 px-4 py-3 text-sm font-semibold tracking-wide text-black shadow-[0_14px_32px_-28px_rgba(185,28,28,0.35)] transition hover:bg-white/85 disabled:opacity-70"
            >
                <span x-show="!resending">RESEND CODE</span>
                <span x-show="resending" class="flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    Resending...
                </span>
                <span class="material-symbols-outlined text-base" x-show="!resending">refresh</span>
            </button>
        </form>

        <a
            href="{{ route('login') }}"
            class="mt-4 flex items-center justify-center gap-2 text-sm font-semibold tracking-wide text-black transition hover:text-black"
        >
            <span class="material-symbols-outlined text-base">arrow_back</span>
            Back to Sign In
        </a>
    </div>

    <div class="flex h-8 w-full items-end justify-center pb-2">
        <div class="h-1 w-32 rounded-full bg-white/20"></div>
    </div>
</div>
@endsection
