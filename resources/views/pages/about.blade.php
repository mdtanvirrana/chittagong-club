@extends('layouts.userpanel')
@section('page_title', 'About CCL')
@section('show_nav', true)
@section('userpanel_content')

    <div class="flex flex-col min-h-screen pb-24">

        {{-- Hero Image --}}
        <div class="relative w-full h-56 overflow-hidden">
            <img src="https://chittagongclubltd.com/wp-content/uploads/2023/04/Drone-Photo-Faisal-Azim-2-768x464-1-600x363.jpg"
                 alt="Chittagong Club"
                 class="w-full h-full object-cover"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
            {{-- Fallback --}}
            <div class="w-full h-full bg-primary/10 border-b border-white/10 items-center justify-center hidden">
                <span class="material-symbols-outlined text-5xl text-primary/30">domain</span>
            </div>
            {{-- Gradient overlay --}}
            <div class="absolute inset-0 bg-gradient-to-t from-[#02568a]/90 via-transparent to-transparent"></div>
            {{-- Badge --}}
            <div class="absolute bottom-4 left-4">
                <p class="text-primary text-[10px] uppercase tracking-[0.2em] font-bold mb-0.5">Est.</p>
                <p class="text-white font-extrabold text-3xl leading-none">1878</p>
            </div>
        </div>

        <main class="px-4 py-6 space-y-6">

            {{-- Tagline --}}
            <div class="text-center px-2">
                <div class="flex items-center justify-center gap-3 mb-3">
                    <span class="h-px w-10 bg-primary/40"></span>
                    <span class="material-symbols-outlined text-primary text-xl">verified_user</span>
                    <span class="h-px w-10 bg-primary/40"></span>
                </div>
                <h2 class="text-white font-extrabold text-xl leading-snug">
                    One of Bangladesh's Most Prestigious Social Clubs
                </h2>
                <p class="text-white/50 text-sm mt-2 leading-relaxed">
                    Founded during British rule, serving members and their families for over 145 years.
                </p>
            </div>

            {{-- Timeline --}}
            <div class="space-y-0">

                @php
                    $timeline = [
                        [
                            'year'  => '1875',
                            'icon'  => 'eco',
                            'title' => 'The Beginning',
                            'body'  => 'Mr. W.A. Campbell, a well-known Tea Planter, started a club placed at the disposal of tea planters to provide logistic services during their stay in the district.',
                        ],
                        [
                            'year'  => '1878',
                            'icon'  => 'apartment',
                            'title' => 'Chittagong Club Founded',
                            'body'  => 'On 23rd August, Campbell\'s Provisional Club transformed into Chittagong Club, opening its doors to all European residents of the district — situated on the hill near the telegraph office, now known as the Forest Bungalow.',
                        ],
                        [
                            'year'  => '1890s',
                            'icon'  => 'foundation',
                            'title' => 'New Club House',
                            'body'  => 'A new Club House was erected at the present location, known as Pioneer Hill — originally a tea garden. The land was generously given to the Club at a nominal charge by the affluent landlord Nityananda Rai Bhahadur.',
                        ],
                        [
                            'year'  => '1901',
                            'icon'  => 'celebration',
                            'title' => 'Grand Opening',
                            'body'  => 'The Club opened at its present premises on 27th March 1901, and was subsequently registered as a company in 1908.',
                        ],
                        [
                            'year'  => '1950',
                            'icon'  => 'gavel',
                            'title' => 'Official Lease',
                            'body'  => 'The Ministry of Defense and the Military granted a 99-year lease in favour of Chittagong Club Limited, executed by registered deed effective from 2nd January 1950.',
                        ],
                        [
                            'year'  => '2012',
                            'icon'  => 'corporate_fare',
                            'title' => 'New Main Club House',
                            'body'  => 'With the growth of membership, a new four-storied Main Club House was built in 2012–13, incorporating an ever-increasing number of facilities.',
                        ],
                    ];
                @endphp

                @foreach ($timeline as $i => $item)
                    <div class="flex gap-4">
                        {{-- Timeline spine --}}
                        <div class="flex flex-col items-center">
                            <div class="shrink-0 size-10 rounded-full bg-primary/10 border border-primary/30 flex items-center justify-center z-10">
                                <span class="material-symbols-outlined text-primary text-base">{{ $item['icon'] }}</span>
                            </div>
                            @if (!$loop->last)
                                <div class="w-px flex-1 bg-white/10 my-1"></div>
                            @endif
                        </div>
                        {{-- Content --}}
                        <div class="flex-1 pb-6">
                            <p class="text-primary text-[10px] font-extrabold uppercase tracking-wider mb-0.5">{{ $item['year'] }}</p>
                            <p class="text-white font-bold text-sm mb-1">{{ $item['title'] }}</p>
                            <p class="text-white/50 text-xs leading-relaxed">{{ $item['body'] }}</p>
                        </div>
                    </div>
                @endforeach

            </div>

            {{-- Today --}}
            <div class="bg-primary/10 border border-primary/20 rounded-2xl p-5 text-center">
                <span class="material-symbols-outlined text-3xl text-primary mb-2 block">groups</span>
                <p class="text-white font-extrabold text-base mb-1">A Legacy That Endures</p>
                <p class="text-white/60 text-sm leading-relaxed">
                    Through the ages, it has become a family Club where members and their families enjoy its array of amenities. The Club continues to uphold its reputation as the foremost institution of its kind in the country.
                </p>
            </div>

            <div class="bg-white/5 border border-white/10 rounded-2xl p-4">
                <p class="text-primary text-[10px] font-bold uppercase tracking-[0.2em]">Trade License</p>
                <p class="mt-2 text-white font-bold text-base">07/2020-2021</p>
            </div>

            {{-- Divider --}}
            <div class="flex items-center gap-3">
                <div class="h-px flex-1 bg-white/10"></div>
                <span class="text-white/20 text-[10px] uppercase tracking-wider">Developed by</span>
                <div class="h-px flex-1 bg-white/10"></div>
            </div>

            {{-- Developer credit --}}
            <div class="bg-white/5 border border-white/10 rounded-2xl p-4 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="size-10 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-white/50 text-xl">code</span>
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm">De Soft Technology Ltd</p>
                        <a href="https://www.de-softbd.com" target="_blank"
                           class="text-primary/70 text-xs">www.de-softbd.com</a>
                    </div>
                </div>
                <div class="divide-y divide-white/5">
                    <a href="mailto:info@de-softbd.com"
                       class="flex items-center gap-3 py-2.5">
                        <span class="material-symbols-outlined text-white/30 text-base shrink-0">mail</span>
                        <span class="text-white/50 text-xs">info@de-softbd.com</span>
                    </a>
                    <a href="tel:+8801746374704"
                       class="flex items-center gap-3 py-2.5">
                        <span class="material-symbols-outlined text-white/30 text-base shrink-0">call</span>
                        <span class="text-white/50 text-xs">+880 1746-374704</span>
                    </a>
                </div>
            </div>

        </main>
    </div>

@endsection
