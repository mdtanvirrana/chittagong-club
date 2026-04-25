@extends('layouts.app')
@section('page_title', 'Set New Password')

@push('styles')
    @include('partials.member-auth-styles')
@endpush

@section('content')
<div
    x-data="{ loading: false }"
    class="relative flex min-h-screen w-full flex-col overflow-hidden blue-depth-gradient"
>
    <div class="h-12 w-full"></div>

    @include('partials.member-auth-hero', [
        'eyebrow' => 'Member First Login',
        'stepLabel' => 'Step 1 of 3',
        'sectionTitle' => 'Send OTP',
        'sectionDescription' => 'Before setting your first password, confirm your member ID with a one-time code sent to your registered mobile number.',
    ])

    <div class="flex flex-1 flex-col justify-start px-8 pb-12">
        <form action="{{ route('password.initial.send') }}" method="POST" class="space-y-6" @submit="loading = true">
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
                <p class="mt-3 text-lg font-bold text-slate-900">{{ data_get($setupState, 'member_id') }}</p>
                <p class="mt-2 text-sm leading-6 text-slate-700">{{ data_get($setupState, 'member_name', 'Member') }}</p>
            </div>

            <div class="auth-floating-card rounded-[1.75rem] px-5 py-5 text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-primary/70">OTP Delivery</p>
                @if ($hasRegisteredPhone)
                    <p class="mt-3 text-2xl font-bold tracking-[0.16em] text-slate-900">{{ data_get($setupState, 'phone.masked') }}</p>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        Tap the button below to send a 6-digit OTP to your registered Bangladesh mobile number.
                    </p>
                @else
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        No registered Bangladesh mobile number is available for this member ID. Contact the club office to continue.
                    </p>
                @endif
            </div>

            <button
                type="submit"
                :disabled="loading || !@js($hasRegisteredPhone)"
                class="flex w-full items-center justify-center gap-2 rounded-xl py-4 font-bold text-brand-blue gold-btn-gradient shadow-[0_10px_20px_-5px_rgba(242,204,13,0.3)] transition-all active:scale-[0.98] disabled:opacity-70"
            >
                <span x-show="!loading">SEND OTP</span>
                <span x-show="loading" class="flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    Sending…
                </span>
                <span class="material-symbols-outlined text-xl" x-show="!loading">sms</span>
            </button>

            <a
                href="{{ route('login') }}"
                class="flex items-center justify-center gap-2 rounded-xl border border-primary/15 bg-white/65 px-4 py-3 text-sm font-semibold tracking-wide text-slate-700 shadow-[0_14px_32px_-28px_rgba(185,28,28,0.35)] transition hover:bg-white/85"
            >
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Back to Sign In
            </a>
        </form>
    </div>

    <div class="flex h-8 w-full items-end justify-center pb-2">
        <div class="h-1 w-32 rounded-full bg-white/20"></div>
    </div>
</div>
@endsection
