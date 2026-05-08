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
        'eyebrow' => 'Member Password Recovery',
        'stepLabel' => 'Step 3 of 3',
        'sectionTitle' => 'Create a New Password',
        'sectionDescription' => 'Your OTP has been confirmed. Set a fresh password to regain access to the member portal.',
    ])

    <div class="flex flex-1 flex-col justify-start px-8 pb-12">
        <form action="{{ route('password.forgot.update') }}" method="POST" class="space-y-6" @submit="loading = true">
            @csrf

            @if ($errors->any())
                <div class="flex items-center gap-3 rounded-xl border border-red-500/30 bg-red-500/15 px-4 py-3">
                    <span class="material-symbols-outlined shrink-0 text-red-400">error</span>
                    <p class="text-sm text-red-700">{{ $errors->first() }}</p>
                </div>
            @endif

            @if ($requiresMemberSelection)
                <div class="auth-floating-card rounded-[1.75rem] px-5 py-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-primary/70">Shared Mobile Number</p>
                    <p class="mt-3 text-sm leading-6 text-black">
                        This Bangladesh mobile number is linked to multiple member logins. Choose the correct member ID before changing the password.
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="ml-1 block text-xs font-semibold uppercase tracking-widest text-primary/80">
                        Member ID
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <span class="material-symbols-outlined login-icon">badge</span>
                        </div>
                        <select
                            name="member_id"
                            class="auth-select block w-full rounded-xl py-4 pl-12 pr-4 focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all {{ $errors->has('member_id') ? 'border-red-500/50' : 'border-primary/10' }}"
                        >
                            <option value="">Select your member ID</option>
                            @foreach (data_get($resetState, 'accounts', []) as $account)
                                <option value="{{ $account['member_id'] }}" @selected(old('member_id') === $account['member_id'])>
                                    {{ $account['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            <div class="space-y-2">
                <label class="block text-xs font-semibold uppercase tracking-widest text-primary/80">New Password</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                        <span class="material-symbols-outlined login-icon">lock</span>
                    </div>
                    <input
                        name="password"
                        :type="showPassword ? 'text' : 'password'"
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
                <span x-show="!loading">SAVE NEW PASSWORD</span>
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
