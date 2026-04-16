@extends('layouts.app')
@section('page_title', 'Club Facilities')
@section('show_nav', true)

@push('styles')
<style>
    .facility-preview-image {
        filter: blur(1.5px) saturate(1.05);
        transform: scale(1.04);
    }

    .facility-preview-overlay {
        background: linear-gradient(180deg, rgba(7, 30, 51, 0.06) 0%, rgba(7, 30, 51, 0.22) 100%);
    }
</style>
@endpush

@php
    $iconMap = [
        'pool' => 'pool',
        'swim' => 'pool',
        'tennis' => 'sports_tennis',
        'gym' => 'fitness_center',
        'fitness' => 'fitness_center',
        'library' => 'menu_book',
        'restaurant' => 'restaurant',
        'dining' => 'restaurant',
        'food' => 'restaurant',
        'room' => 'king_bed',
        'suite' => 'king_bed',
        'guest' => 'king_bed',
        'hall' => 'celebration',
        'banquet' => 'celebration',
        'spa' => 'spa',
        'wellness' => 'spa',
        'play' => 'sports_esports',
        'billiard' => 'sports_bar',
        'bar' => 'sports_bar',
        'court' => 'stadium',
    ];
@endphp

@section('content')
<div class="flex flex-col min-h-screen pb-24">
    <header class="sticky top-0 z-50 bg-background-dark/80 ios-blur border-b border-primary/20">
        <div class="flex items-center justify-between px-4 h-16">
            <a href="{{ route('dashboard') }}" class="flex items-center justify-center p-2 rounded-full hover:bg-primary/10 transition-colors">
                <span class="material-symbols-outlined text-primary">arrow_back_ios</span>
            </a>
            <div class="flex flex-col items-center">
                <span class="text-[10px] uppercase tracking-[0.2em] text-primary font-bold">{{ $companyName }}</span>
                <h1 class="text-lg font-bold leading-tight">Club Facilities</h1>
            </div>
            <div class="size-10"></div>
        </div>
    </header>

    <main class="flex-1 p-4 mb-4">
        @if ($facilities->isEmpty())
            <div class="rounded-2xl border border-primary/20 bg-primary/5 px-5 py-10 text-center">
                <span class="material-symbols-outlined text-primary text-4xl">apartment</span>
                <h2 class="mt-3 text-lg font-bold text-white">No facilities found</h2>
                <p class="mt-2 text-sm text-white/60">Departments from `List_Department` will appear here automatically.</p>
            </div>
        @else
            <div class="grid grid-cols-2 gap-4">
                @foreach ($facilities as $facility)
                    @php
                        $normalizedName = strtolower($facility['name']);
                        $icon = 'apartment';

                        foreach ($iconMap as $keyword => $mappedIcon) {
                            if (str_contains($normalizedName, $keyword)) {
                                $icon = $mappedIcon;
                                break;
                            }
                        }

                        $isFeatured = $loop->iteration % 5 === 0;
                    @endphp

                    <div class="relative group {{ $isFeatured ? 'aspect-square col-span-2' : 'aspect-[3/4]' }} rounded-xl overflow-hidden border border-primary/20">
                        <div class="absolute inset-0 bg-cover bg-center facility-preview-image"
                             style="background-image: url('{{ $facility['image_url'] }}')">
                        </div>
                        <div class="absolute inset-0 facility-preview-overlay transition-opacity group-active:opacity-90"></div>
                        <div class="absolute inset-0 flex flex-col justify-end {{ $isFeatured ? 'p-6' : 'p-4' }}">
                            <span class="material-symbols-outlined text-primary {{ $isFeatured ? 'text-4xl' : 'text-3xl' }} mb-2">{{ $icon }}</span>
                            <h3 class="text-white {{ $isFeatured ? 'text-2xl' : 'text-lg' }} font-bold leading-tight">{{ $facility['name'] }}</h3>

                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>
</div>
@endsection
