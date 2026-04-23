@extends('layouts.userpanel')
@section('page_title', 'Dress Code')
@section('show_nav', true)
@section('userpanel_content')

    <div class="flex flex-col min-h-screen pb-24">
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
