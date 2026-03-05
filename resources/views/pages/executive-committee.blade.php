@extends('layouts.app')
@section('title', 'Executive Committee — Chittagong Club Ltd.')
@section('show_nav', true)

@section('content')
<div class="flex flex-col min-h-screen pb-24">

    {{-- Sticky Header --}}
    <div class="sticky top-0 z-50 bg-brand-blue/90 ios-blur flex items-center p-4 justify-between border-b border-white/10">
        <a href="{{ route('dashboard') }}" class="text-white flex size-10 items-center justify-center rounded-full hover:bg-white/10 cursor-pointer">
            <span class="material-symbols-outlined">arrow_back_ios_new</span>
        </a>
        <h2 class="text-white text-lg font-bold leading-tight tracking-tight flex-1 text-center">
            Executive Committee
        </h2>
        <button class="flex items-center justify-center size-10 rounded-full hover:bg-white/10">
            <span class="material-symbols-outlined text-club-gold">search</span>
        </button>
    </div>

    <main class="flex flex-col gap-6 p-4 pb-8">

        {{-- Title --}}
        <div class="flex flex-col items-center justify-center py-4">
            <div class="flex items-center gap-2 mb-1">
                <span class="h-px w-8 bg-club-gold/40"></span>
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-club-gold">Chittagong Club Ltd</span>
                <span class="h-px w-8 bg-club-gold/40"></span>
            </div>
            <h1 class="text-3xl font-extrabold text-white">Leadership</h1>
            <p class="text-club-gold/80 text-sm mt-1 font-medium tracking-wide">Academic Year 2023 – 2024</p>
        </div>

        {{-- President Card --}}
        <div class="relative overflow-hidden rounded-xl card-glass p-6 flex flex-col items-center text-center shadow-2xl">
            <div class="absolute -top-10 -right-10 size-32 rounded-full bg-club-gold/10 blur-3xl"></div>
            <div class="relative">
                <div class="size-32 rounded-full p-1 bg-gradient-to-tr from-club-gold to-yellow-200">
                    <div class="size-full rounded-full bg-center bg-cover border-4 border-brand-blue"
                         style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDsomTeAURnjSa6-HsDdKb8vNq7SUso7VRu-1PTVaww7mqzivIya44CK6dpmvFWvSUmRaRHXDE1A1EhIpP3DQW9rxlVYeOeczjKFYlf6vvCBg4qRlf892UsHrxGPi34eeaX4O0gBbdjHcn-3fumJ5kBb27r9gDK7BtgYXG0p9vGBo5v8niATOQN6bytEuqJ-9I4LI8RJDlOB7k5RqT1Jnok2wGAFcbgYjIhlBzLYhnNh-tKM38HZWGDFbYGcm4rxpEd6D5eQuM9kGE')">
                    </div>
                </div>
                <div class="absolute -bottom-2 right-0 bg-club-gold text-brand-blue size-8 rounded-full flex items-center justify-center shadow-lg border-2 border-brand-blue">
                    <span class="material-symbols-outlined text-sm font-bold">gavel</span>
                </div>
            </div>
            <div class="mt-4 flex flex-col gap-1">
                <p class="text-white text-2xl font-bold leading-tight tracking-tight">Mr. Salahuddin Kasem Khan</p>
                <p class="text-club-gold text-lg font-semibold uppercase tracking-wider">President</p>
            </div>
            <div class="mt-6 flex gap-3 w-full">
                <button class="flex-1 flex items-center justify-center gap-2 bg-club-gold text-brand-blue h-11 rounded-lg font-bold transition-transform active:scale-95">
                    <span class="material-symbols-outlined text-lg">mail</span>
                    Contact
                </button>
                <button class="size-11 flex items-center justify-center card-glass rounded-lg text-club-gold transition-transform active:scale-95">
                    <span class="material-symbols-outlined">call</span>
                </button>
            </div>
        </div>

        {{-- Section heading --}}
        <div class="flex items-center gap-3 mt-2">
            <h3 class="text-white text-xl font-bold tracking-tight">Council Members</h3>
            <div class="h-px flex-1 bg-white/10"></div>
        </div>

        {{-- Council Members --}}
        @php
        $members = [
            ['name' => 'Mr. Farhan Hossain',       'role' => 'Vice President',       'icon' => 'star',        'photo' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuABaHOHZmCTVbzHKR_wt_j7w7w4G07U747WAYAodYi7oL0Er5O3DpgF1AHNGEH-TjvTWTyKYuTX9xAz_twYSEjEssQjMtaYMvQuvHZP-Wm1eeq7kUj3qQ92Ye-q_6A-pEdaDnCDc8cw6di5NwFRmB_8qGs4PJbYyUXHFWIihyhrp7EpzOTge6cKBc7GhIsdH4pmejOm7aAqm6a6DP68oKWA7XR-pLiaD-YB4tAPicA_prinz0QXOxPuePCpbYLnLS9oz8VZlmurNik'],
            ['name' => 'Mr. Zahid Islam',           'role' => 'Honorary Secretary',  'icon' => 'ink_pen',     'photo' => ''],
            ['name' => 'Mr. Asif Ahmed Chowdhury',  'role' => 'Treasurer',            'icon' => 'payments',    'photo' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuD4mvvYxvDKpxNWeNjMzQNbGn8JeqC1rwZb7YB9sPfBb8wpRFOGcPfNO_tjLwRBScpSYJEKelqfYQ3519Y1rDAU4VOMrSbRwja9B8I4jigb71GU9Ny9yCyfhnPTuaBKwOqgBEIVjyYcnnuhlgEBWOd-CSxfo1Lqon6Ze65XrGCNiNsShC0Vv2RUHG3ZkITgxXfmnImN6PsxIaPNd92vG8gFh7qxHPL0fNAWYFmZ6a6ResSbyvjSjWLmiJh9vFOI00n1BHIeBVJMuF4'],
            ['name' => 'Dr. S. M. Kamal',           'role' => 'Member Council',       'icon' => 'group',       'photo' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDNB3FyWxPF9VJThBhXfD4FVkyq6d3ZVlzHAtszozsuhL02SaoRGW25IUWuatrkZbZMTgpiDySQeK5fc2_FS3THI0ZJw4GPWM4MOvUjWKIKNY0bZ6KD4xYgUkgQHMWoDoWlZFlh3D6gEWh5SW2MNS6Tke1_z7HIiiamPgMIiCH_KVhkEHWrqcQSh-QvoSCGT4f1l4BeI3Sqwgjg9ZPx3bCKj5eKSq1SztkS1wKW3K1KX1__ZF6BhQSUVHIAILlZbzzvsE6yOVdNlI8'],
        ];
        @endphp

        <div class="grid gap-4">
            @foreach ($members as $m)
            <div class="flex items-center gap-4 rounded-xl card-glass p-4">
                <div class="size-20 shrink-0 rounded-lg p-0.5 bg-club-gold/30">
                    @if($m['photo'])
                    <div class="size-full rounded-lg bg-center bg-cover" style="background-image: url('{{ $m['photo'] }}')"></div>
                    @else
                    <div class="size-full rounded-lg bg-white/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-club-gold text-3xl">person</span>
                    </div>
                    @endif
                </div>
                <div class="flex flex-col flex-1 overflow-hidden">
                    <p class="text-white text-base font-bold leading-tight truncate">{{ $m['name'] }}</p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="material-symbols-outlined text-xs text-club-gold">{{ $m['icon'] }}</span>
                        <p class="text-club-gold/80 text-sm font-medium">{{ $m['role'] }}</p>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button class="px-3 py-1.5 rounded-md bg-white/10 text-white text-xs font-semibold flex items-center gap-1.5 hover:bg-white/20 transition-colors">
                            <span class="material-symbols-outlined text-[14px]">alternate_email</span>
                            Message
                        </button>
                        <button class="px-3 py-1.5 rounded-md bg-white/10 text-white text-xs font-semibold flex items-center gap-1.5 hover:bg-white/20 transition-colors">
                            <span class="material-symbols-outlined text-[14px]">info</span>
                            Details
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Footer --}}
        <div class="mt-8 flex flex-col items-center text-center opacity-40 px-8 italic">
            <span class="material-symbols-outlined text-3xl mb-2 text-club-gold">verified_user</span>
            <p class="text-xs">Upholding the prestige and legacy of Chittagong Club Ltd since 1878.</p>
        </div>

    </main>
</div>
@endsection
