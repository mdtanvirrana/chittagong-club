@extends('layouts.app')
@section('title', 'Login — Chittagong Club Ltd.')

@push('styles')
<style>
    .blue-depth-gradient { background: radial-gradient(circle at center, #116fa7 0%, #0c5c8b 100%); }
    .gold-text-gradient {
        background: linear-gradient(135deg, #f2cc0d 0%, #fff3b0 50%, #d4af37 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .gold-btn-gradient { background: linear-gradient(135deg, #f2cc0d 0%, #ffdf40 50%, #d4af37 100%); }
</style>
@endpush

@section('content')
<div
    x-data="{
        showPassword: false,
        loading: false,
        memberId: '',
        password: '',
    }"
    class="relative flex h-screen w-full flex-col blue-depth-gradient overflow-hidden"
>
    {{-- Status bar spacer --}}
    <div class="h-12 w-full"></div>



    {{-- Hero / Logo --}}
    <div class="flex flex-col items-center justify-center pt-8 pb-12 px-8">
        <div class="relative mb-8">
            <div class="absolute -inset-4 bg-primary/10 blur-3xl rounded-full opacity-50"></div>
            <div class="relative flex items-center justify-center w-32 h-32">
                <img
                    class="w-24 h-24 object-contain rounded-full"
                    src="{{asset('logo.jpg')}}"
                    alt="Chittagong Club Logo"
                />
            </div>
        </div>
        <h1 class="text-4xl font-extrabold tracking-tight text-center mb-2">
            <span class="gold-text-gradient">Chittagong Club</span>
        </h1>
        <p class="text-white/60 text-sm font-light tracking-widest uppercase text-center">
            Exclusive Member Access
        </p>
    </div>

    {{-- Login Form --}}
    <div class="flex-1 px-8 pb-12 flex flex-col justify-start w-full">
        <form action="{{ route('login.post') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Error alert --}}
            @if ($errors->any())
            <div class="flex items-center gap-3 bg-red-500/15 border border-red-500/30 rounded-xl px-4 py-3">
                <span class="material-symbols-outlined text-red-400 shrink-0">error</span>
                <p class="text-red-300 text-sm">{{ $errors->first() }}</p>
            </div>
            @endif

            {{-- Membership ID --}}
            <div class="space-y-2">
                <label class="block text-xs font-semibold text-primary/80 uppercase tracking-widest ml-1">
                    Membership ID
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-white/40 group-focus-within:text-primary transition-colors">badge</span>
                    </div>
                    <input
                        name="member_id"
                        type="text"
                        value="{{ old('member_id') }}"
                        placeholder="e.g. CCL-88291"
                        autocomplete="username"
                        class="block w-full bg-white/5 border {{ $errors->has('member_id') ? 'border-red-500/50' : 'border-white/10' }} rounded-xl py-4 pl-12 pr-4 text-white placeholder:text-white/20 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary/50 transition-all"
                    />
                </div>
            </div>

            {{-- Password --}}
            <div class="space-y-2">
                <label class="block text-xs font-semibold text-primary/80 uppercase tracking-widest">Security Code</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-white/40 group-focus-within:text-primary transition-colors">lock</span>
                    </div>
                    <input
                        name="password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        class="block w-full bg-white/5 border border-white/10 rounded-xl py-4 pl-12 pr-12 text-white placeholder:text-white/20 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary/50 transition-all"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center"
                    >
                        <span class="material-symbols-outlined text-white/40 hover:text-white"
                              x-text="showPassword ? 'visibility' : 'visibility_off'">
                        </span>
                    </button>
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
    </div>

    {{-- iOS home indicator --}}
    <div class="h-8 w-full flex justify-center items-end pb-2">
        <div class="w-32 h-1 bg-white/20 rounded-full"></div>
    </div>
</div>
@endsection
