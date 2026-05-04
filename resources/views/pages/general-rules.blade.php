@extends('layouts.userpanel')
@section('page_title', 'General Rules')
@section('show_nav', true)
@section('userpanel_content')

    <div class="flex flex-col min-h-screen pb-24">
        <main class="space-y-4 px-4 py-5">
            @forelse ($images as $image)
                <img
                    src="{{ $image }}"
                    alt="General Rules"
                    class="w-full rounded-2xl border border-white/10"
                    loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                >
            @empty
                <div class="flex w-full flex-col items-center justify-center gap-3 rounded-2xl border border-white/10 bg-white/5 py-20 text-center">
                    <span class="material-symbols-outlined text-5xl text-white/20">gavel</span>
                    <p class="text-sm text-white/30">Image not available</p>
                </div>
            @endforelse
        </main>

    </div>

@endsection
