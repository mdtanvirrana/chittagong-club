@extends('layouts.app')
@section('title', 'Contact — Chittagong Club Ltd.')
@section('show_nav', true)

@section('content')
<div class="flex flex-col min-h-screen pb-24">

    {{-- Header --}}
    <div class="fixed top-0 z-50 w-full max-w-[425px] flex items-center justify-between px-4 py-4 bg-brand-blue/80 ios-blur border-b border-white/10">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="flex items-center justify-center rounded-full p-1 text-white">
                <span class="material-symbols-outlined text-[28px]">chevron_left</span>
            </a>
            <h1 class="text-lg font-bold tracking-tight text-white">Contact &amp; Info</h1>
        </div>
        <button class="text-club-gold">
            <span class="material-symbols-outlined">share</span>
        </button>
    </div>

    {{-- Hero Map --}}
    <div class="relative h-[40vh] w-full pt-16">
        <div class="absolute inset-0 z-0 bg-cover bg-center"
             style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCAV4OPFWDT-BcpdKTbxmvkjFOxK8ii8F1WRamzYjeIoyT0I1wIpcPtpFJnRHhyRqi9kJZmwYyNfUNnh02oosVZvA0aY7KsmUphrZiztEbk-Ox9Bbe4VEfwf2jYFW9r32Z0TRHvck_xjUr4e00vuIvbEon8XnXzL3h3A-tf4s_Je1mPdggSycsyMpXYyWuNwxsY_Fb5WvMFfaZZIVd0YyWv_0m8u9Gc6uDvwuNBs8TFgEmvR2EXxjbC9gqEMaqb6LSNtj-WGClZOs4')">
        </div>
        <div class="absolute inset-0 flex items-center justify-center z-10 pointer-events-none pb-12">
            <div class="flex flex-col items-center">
                <div class="bg-brand-blue text-club-gold p-2 rounded-full shadow-xl border-2 border-club-gold">
                    <span class="material-symbols-outlined text-[32px] font-bold">location_on</span>
                </div>
                <div class="mt-2 bg-white/90 px-3 py-1 rounded-full shadow-lg border border-club-gold/30">
                    <span class="text-xs font-bold text-brand-blue">Chittagong Club</span>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-32 z-20"
             style="background: linear-gradient(to bottom, rgba(2,86,138,0) 0%, rgba(10,61,98,1) 100%)">
        </div>
    </div>

    {{-- Info Card --}}
    <div class="relative z-30 -mt-10 mb-8 px-4">
        <div class="rounded-xl bg-white/10 border border-white/10 shadow-xl overflow-hidden">

            {{-- Header band --}}
            <div class="bg-brand-blue p-6">
                <h2 class="text-xl font-bold text-white leading-tight">Chittagong Club Ltd.</h2>
                <p class="text-club-gold text-sm font-medium mt-1 uppercase tracking-widest">Heritage &amp; Excellence Since 1878</p>
            </div>

            {{-- Address --}}
            <div class="p-6 border-b border-white/10">
                <div class="flex items-start gap-4">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-club-gold/10 text-club-gold">
                        <span class="material-symbols-outlined">map</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-club-gold uppercase tracking-tighter mb-1">Location</h3>
                        <p class="text-base font-medium leading-relaxed text-white">
                            1-5 S.S. Khaled Road, Lalkhan Bazar,<br/>Chittagong, Bangladesh
                        </p>
                        <button class="mt-4 flex items-center gap-2 text-primary font-bold text-sm">
                            <span class="material-symbols-outlined text-sm">directions</span>
                            Get Directions
                        </button>
                    </div>
                </div>
            </div>

            {{-- Phone & Email --}}
            <div class="p-6 border-b border-white/10">
                <h3 class="text-xs font-bold text-club-gold uppercase tracking-widest mb-4">Reception &amp; Services</h3>

                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-club-gold/10 text-club-gold">
                            <span class="material-symbols-outlined">call</span>
                        </div>
                        <div>
                            <p class="text-xs text-white/50">Primary Desk</p>
                            <p class="text-base font-bold text-white">+880 31 611251-53</p>
                        </div>
                    </div>
                    <a href="tel:+88031611251" class="rounded-full bg-brand-blue px-4 py-2 text-xs font-bold text-white">Call</a>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-club-gold/10 text-club-gold">
                            <span class="material-symbols-outlined">mail</span>
                        </div>
                        <div>
                            <p class="text-xs text-white/50">General Inquiry</p>
                            <p class="text-base font-bold text-white">info@chittagongclub.com</p>
                        </div>
                    </div>
                    <a href="mailto:info@chittagongclub.com" class="rounded-full border-2 border-white/30 px-4 py-2 text-xs font-bold text-white">Email</a>
                </div>
            </div>

            {{-- Departments --}}
            <div class="p-6 bg-white/5">
                <h3 class="text-xs font-bold text-club-gold uppercase tracking-widest mb-4">Department Extensions</h3>
                <div class="grid grid-cols-2 gap-4">
                    @php
                    $depts = [
                        ['name' => 'Member Services', 'ext' => '102'],
                        ['name' => 'Dining Hall',     'ext' => '205'],
                        ['name' => 'Sports Complex',  'ext' => '310'],
                        ['name' => 'Library',          'ext' => '401'],
                    ];
                    @endphp
                    @foreach ($depts as $d)
                    <div class="rounded-lg bg-white/5 border border-white/10 p-3">
                        <p class="text-xs text-white/50">{{ $d['name'] }}</p>
                        <p class="text-sm font-bold text-white">Ext: {{ $d['ext'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Hours --}}
            <div class="p-6">
                <div class="flex items-center gap-4 mb-3">
                    <span class="material-symbols-outlined text-club-gold">schedule</span>
                    <h3 class="text-sm font-bold text-club-gold uppercase tracking-widest">Operating Hours</h3>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-white/50">Sunday – Thursday</span>
                        <span class="font-medium text-white">08:00 AM – 11:00 PM</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-white/50">Friday – Saturday</span>
                        <span class="font-medium text-white">07:00 AM – 12:00 AM</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
