@extends('layouts.userpanel')
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

@section('userpanel_content')
<div class="flex flex-col min-h-screen pb-24">
    <main id="top" class="flex-1 p-4 mb-4">
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
                        $hasDepartmentImages = ! empty($facility['images']);

                        foreach ($iconMap as $keyword => $mappedIcon) {
                            if (str_contains($normalizedName, $keyword)) {
                                $icon = $mappedIcon;
                                break;
                            }
                        }

                        $isFeatured = $loop->iteration % 5 === 0;
                    @endphp

                    <a
                        href="{{ $hasDepartmentImages ? '#facility-section-'.$facility['id'] : '#' }}"
                        class="relative group {{ $isFeatured ? 'aspect-square col-span-2' : 'aspect-[3/4]' }} rounded-xl overflow-hidden border border-primary/20 bg-white/[0.04] {{ $hasDepartmentImages ? 'cursor-pointer' : 'pointer-events-none' }}"
                    >
                        @if (! empty($facility['image_url']))
                            <div class="absolute inset-0 bg-cover bg-center facility-preview-image"
                                 style="background-image: url('{{ $facility['image_url'] }}')">
                            </div>
                            <div class="absolute inset-0 facility-preview-overlay transition-opacity group-active:opacity-90"></div>
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-white/[0.08] to-primary/[0.08]"></div>
                        @endif
                        <div class="absolute inset-0 flex flex-col justify-end {{ $isFeatured ? 'p-6' : 'p-4' }}">
                            <span class="material-symbols-outlined text-primary {{ $isFeatured ? 'text-4xl' : 'text-3xl' }} mb-2">{{ $icon }}</span>
                            <h3 class="text-white {{ $isFeatured ? 'text-2xl' : 'text-lg' }} font-bold leading-tight">{{ $facility['name'] }}</h3>

                        </div>
                    </a>
                @endforeach
            </div>

            @php
                $facilitiesWithImages = $facilities->filter(fn ($facility) => ! empty($facility['images']));
            @endphp

            @if ($facilitiesWithImages->isNotEmpty())
                <div class="mt-8 space-y-5">
                    @foreach ($facilitiesWithImages as $facility)
                        <section id="facility-section-{{ $facility['id'] }}" class="scroll-mt-6 rounded-2xl border border-primary/20 bg-white/[0.04] p-4">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <h2 class="text-lg font-bold text-white">{{ $facility['name'] }}</h2>
                                <a href="#top" class="text-xs font-semibold uppercase tracking-[0.16em] text-primary">Top</a>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                @foreach ($facility['images'] as $image)
                                    <a href="{{ $image }}" target="_blank" rel="noreferrer" class="block overflow-hidden rounded-xl border border-white/10 bg-slate-950/30">
                                        <img src="{{ $image }}" alt="{{ $facility['name'] }}" class="aspect-square h-full w-full object-cover" loading="lazy">
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            @endif
        @endif
    </main>
</div>
@endsection
