@extends('layouts.userpanel')
@section('page_title', 'Dress Code')
@section('show_nav', true)
@section('userpanel_content')

    <div class="flex flex-col min-h-screen pb-24">

        <header class="userpanel-subheader sticky top-0 z-50 bg-brand-blue/90 ios-blur border-b border-white/10 px-4 pt-12 pb-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('dashboard') }}"
                   class="flex size-10 items-center justify-center rounded-full hover:bg-white/10 transition-colors">
                    <span class="material-symbols-outlined text-white">arrow_back_ios</span>
                </a>
                <div class="text-center">
                    <p class="text-primary text-[10px] uppercase tracking-[0.2em] font-bold">{{ $companyName }}</p>
                    <h1 class="text-white text-lg font-bold">Dress Code</h1>
                </div>
                <div class="size-10"></div>
            </div>
        </header>

        <main class="px-4 py-5">
            <img src="{{ asset('images/dress-code/dress-code.png') }}"
                 alt="Dress Code"
                 class="w-full rounded-2xl border border-white/10"
                 >
            <div class="w-full rounded-2xl border border-white/10 bg-white/5 py-20 flex-col items-center justify-center gap-3 hidden">
                <span class="material-symbols-outlined text-5xl text-white/20">checkroom</span>
                <p class="text-white/30 text-sm">Image not available</p>
            </div>
        </main>

    </div>

@endsection
