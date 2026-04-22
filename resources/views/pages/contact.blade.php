@extends('layouts.userpanel')
@section('page_title', 'Contact')
@section('show_nav', true)
@section('userpanel_content')

    <div class="flex flex-col min-h-screen pb-24">
        <main class="px-4 py-5 space-y-5">

            {{-- Address card --}}
            <a href="{{ 'https://maps.google.com/?q=' . urlencode($companyAddressMapQuery) }}"
               target="_blank"
               class="flex items-start gap-4 bg-white/5 border border-white/10 rounded-2xl p-4">
                <div class="shrink-0 size-11 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-xl">location_on</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white/40 text-[10px] uppercase tracking-wider font-bold mb-1">Address</p>
                    <p class="text-white font-semibold text-sm leading-relaxed">
                        {{ $companyName }}@if ($companyAddressLines)<br>@endif
                        @foreach ($companyAddressLines as $line)
                            {{ $line }}@if (! $loop->last)<br>@endif
                        @endforeach
                    </p>
                </div>
                <span class="material-symbols-outlined text-white/20 shrink-0 mt-1">open_in_new</span>
            </a>

            {{-- ── Main Club ────────────────────────────── --}}
            <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden">

                {{-- Section heading --}}
                <div class="flex items-center gap-3 px-4 py-3 border-b border-white/10 bg-white/5">
                    <div class="size-8 rounded-lg bg-primary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary text-base">domain</span>
                    </div>
                    <div>
                        <p class="text-white font-extrabold text-sm">Main Club</p>
                        <p class="text-white/40 text-[10px]">For any query please contact</p>
                    </div>
                </div>

                <div class="divide-y divide-white/5">

                    {{-- General lines --}}
                    @foreach ([
                        '+88 02333388078',
                        '+88 02333388079',
                        '+88 02333388080',
                        '+88 02333388081',
                    ] as $num)
                        <a href="tel:{{ preg_replace('/\s+/','',$num) }}"
                           class="flex items-center gap-3 px-4 py-3 active:bg-white/5 transition-colors">
                            <span class="material-symbols-outlined text-primary text-base shrink-0">call</span>
                            <span class="text-white text-sm">{{ $num }}</span>
                        </a>
                    @endforeach

                    {{-- Auto hunting --}}
                    <div class="px-4 pt-3 pb-1">
                        <p class="text-white/30 text-[10px] uppercase tracking-wider font-bold">Auto Hunting</p>
                    </div>
                    <a href="tel:+880233338083"
                       class="flex items-center gap-3 px-4 py-3 active:bg-white/5 transition-colors">
                        <span class="material-symbols-outlined text-primary text-base shrink-0">call</span>
                        <span class="text-white text-sm">+88 02333388083</span>
                    </a>

                    {{-- Venue booking --}}
                    <div class="px-4 pt-3 pb-1">
                        <p class="text-white/30 text-[10px] uppercase tracking-wider font-bold">Venue Booking</p>
                    </div>
                    <a href="tel:+8801755665150"
                       class="flex items-center gap-3 px-4 py-3 active:bg-white/5 transition-colors">
                        <span class="material-symbols-outlined text-primary text-base shrink-0">call</span>
                        <span class="text-white text-sm">+88 01755665150</span>
                    </a>

                    {{-- bKash / Nagad --}}
                    <div class="px-4 pt-3 pb-1">
                        <p class="text-white/30 text-[10px] uppercase tracking-wider font-bold">bKash &amp; Nagad (Bill Payment Only)</p>
                    </div>
                    <a href="tel:+8801844667014"
                       class="flex items-center gap-3 px-4 py-3 active:bg-white/5 transition-colors">
                        <span class="material-symbols-outlined text-primary text-base shrink-0">payments</span>
                        <span class="text-white text-sm">+88 01844667014</span>
                    </a>

                    {{-- Secretary --}}
                    <div class="px-4 pt-3 pb-1">
                        <p class="text-white/30 text-[10px] uppercase tracking-wider font-bold">Secretary</p>
                    </div>
                    <div class="px-4 pb-2">
                        <p class="text-white/60 text-xs">Cdr Md. Ashraf Uddin, (C), psc, BN (Retd)</p>
                    </div>
                    <a href="tel:+8801713123124"
                       class="flex items-center gap-3 px-4 py-3 active:bg-white/5 transition-colors">
                        <span class="material-symbols-outlined text-primary text-base shrink-0">call</span>
                        <span class="text-white text-sm">+880 1713123124</span>
                    </a>

                    {{-- Email --}}
                    <a href="mailto:chittagongclub@gmail.com"
                       class="flex items-center gap-3 px-4 py-3 active:bg-white/5 transition-colors">
                        <span class="material-symbols-outlined text-primary text-base shrink-0">mail</span>
                        <span class="text-white text-sm">chittagongclub@gmail.com</span>
                    </a>

                </div>
            </div>


            {{-- ── Guest House Complex ──────────────────── --}}
            <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden">

                <div class="flex items-center gap-3 px-4 py-3 border-b border-white/10 bg-white/5">
                    <div class="size-8 rounded-lg bg-primary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary text-base">hotel</span>
                    </div>
                    <div>
                        <p class="text-white font-extrabold text-sm">Guest House Complex</p>
                        <p class="text-white/40 text-[10px]">For query / reservation please contact</p>
                    </div>
                </div>

                <div class="divide-y divide-white/5">

                    {{-- Front desk heading --}}
                    <div class="px-4 pt-3 pb-1">
                        <p class="text-white/30 text-[10px] uppercase tracking-wider font-bold">Front Desk (Reception)</p>
                    </div>
                    @foreach (['+88 02333388084', '+88 02333388085'] as $num)
                        <a href="tel:{{ preg_replace('/\s+/','',$num) }}"
                           class="flex items-center gap-3 px-4 py-3 active:bg-white/5 transition-colors">
                            <span class="material-symbols-outlined text-primary text-base shrink-0">call</span>
                            <span class="text-white text-sm">{{ $num }}</span>
                        </a>
                    @endforeach

                    {{-- Guest house direct --}}
                    <div class="px-4 pt-3 pb-1">
                        <p class="text-white/30 text-[10px] uppercase tracking-wider font-bold">Guest House Direct</p>
                    </div>
                    <a href="tel:+8801714080714"
                       class="flex items-center gap-3 px-4 py-3 active:bg-white/5 transition-colors">
                        <span class="material-symbols-outlined text-primary text-base shrink-0">call</span>
                        <span class="text-white text-sm">+88 01714080714</span>
                    </a>

                    {{-- Email --}}
                    <a href="mailto:ghcchittagongclub@gmail.com"
                       class="flex items-center gap-3 px-4 py-3 active:bg-white/5 transition-colors">
                        <span class="material-symbols-outlined text-primary text-base shrink-0">mail</span>
                        <span class="text-white text-sm">ghcchittagongclub@gmail.com</span>
                    </a>

                </div>
            </div>

            {{-- Footer note --}}
            <div class="flex flex-col items-center text-center py-4 opacity-40">
                <span class="material-symbols-outlined text-2xl text-primary mb-2">verified_user</span>
                <p class="text-xs italic">Upholding the prestige and legacy of {{ $companyName }} since 1878.</p>
            </div>

        </main>
    </div>

@endsection
