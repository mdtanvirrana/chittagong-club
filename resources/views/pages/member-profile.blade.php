@extends('layouts.app')
@section('title', 'My Profile — Chittagong Club Ltd.')
@section('show_nav', true)

@push('styles')
    <style>
        .gold-border-thick {
            border: 4px solid #f2d00d;
            box-shadow: 0 0 15px rgba(242, 208, 13, 0.3);
        }
    </style>
@endpush

@php
    $fullName  = trim(($member->Title ? $member->Title.' ' : '') . $member->CusName);
    $initials  = collect(explode(' ', $member->CusName))
                    ->map(fn($w) => strtoupper($w[0] ?? ''))
                    ->take(2)->join('');

    $joinDate  = $member->DOE      ? \Carbon\Carbon::parse($member->DOE)->format('M d, Y')      : '—';
    $birthDate = $member->BirthDt  ? \Carbon\Carbon::parse($member->BirthDt)->format('M d, Y')  : '—';
    $age       = $member->BirthDt  ? \Carbon\Carbon::parse($member->BirthDt)->age . ' yrs'      : '—';
    $weddingDt = $member->MarriageDT ? \Carbon\Carbon::parse($member->MarriageDT)->format('M d, Y') : '—';
    $isMarried = in_array(strtolower($member->MaritalStatus ?? ''), ['m', 'married']);

    $statusColor = match(strtolower($member->MemExpTypeName ?? '')) {
        'active'  => 'bg-green-500/20 text-green-400',
        'expired' => 'bg-red-500/20 text-red-400',
        default   => 'bg-amber-500/20 text-amber-400',
    };
@endphp

@section('content')
    <div class="flex flex-col min-h-screen pb-24">

        {{-- Blue Header --}}
        <div class="bg-brand-blue w-full pt-4 pb-14 px-4 rounded-b-[2.5rem] shadow-2xl">
            <div class="flex items-center justify-between mb-8">
                <a href="{{ route('dashboard') }}"
                   class="text-white flex size-10 items-center justify-center rounded-full bg-white/10 ios-blur">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <h2 class="text-white text-lg font-bold tracking-tight">Member Profile</h2>
                <div class="size-10"></div>
            </div>

            <div class="flex flex-col items-center">
                <div class="relative">
                    <div
                        class="gold-border-thick rounded-full h-28 w-28 mb-4 overflow-hidden flex items-center justify-center bg-brand-blue/80">
                        <span class="text-primary font-extrabold text-4xl">{{ $initials }}</span>
                    </div>
                    <div
                        class="absolute -bottom-1 right-0 gold-gradient px-3 py-1 rounded-full shadow-lg flex items-center gap-1 border border-white/20">
                        <span class="material-symbols-outlined text-[14px] font-bold text-brand-blue">verified</span>
                        <span
                            class="text-[10px] font-extrabold uppercase tracking-wider text-brand-blue">Verified</span>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <h1 class="text-white text-2xl font-extrabold tracking-tight">{{ $fullName }}</h1>
                    <p class="text-primary font-semibold text-sm tracking-[0.1em] uppercase mt-1">
                        {{ $member->MemberCategory ?? 'Member' }} • {{ $member->PrvCusID }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="px-4 -mt-6 relative z-10 space-y-4">

            {{-- Quick stat chips --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-white/10 rounded-xl p-3 text-center border border-white/10">
                    <p class="text-white/40 text-[10px] font-bold uppercase tracking-wider mb-1">Joined</p>
                    <p class="text-primary text-sm font-bold">
                        {{ $member->DOE ? \Carbon\Carbon::parse($member->DOE)->format('Y') : '—' }}
                    </p>
                </div>
                <div class="bg-white/10 rounded-xl p-3 text-center border border-white/10">
                    <p class="text-white/40 text-[10px] font-bold uppercase tracking-wider mb-1">Status</p>
                    <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full {{ $statusColor }}">
                    {{ $member->MemExpTypeName ?? 'N/A' }}
                </span>
                </div>

            </div>

            {{-- Personal Info --}}
            @include('pages.partials.profile-card', [
                'icon'  => 'person',
                'title' => 'Personal Information',
                'rows'  => [
                    ['label' => 'Full Name',     'value' => $fullName],
                    ['label' => 'Date of Birth', 'value' => $birthDate . ($age !== '—' ? " ($age)" : '')],
                    ['label' => 'Blood Group',   'value' => $member->BloodGroup  ?: '—'],
                    ['label' => 'Gender',        'value' => match(strtolower($member->Sex ?? '')) {
                                                                'm' => 'Male', 'f' => 'Female', default => $member->Sex ?: '—'
                                                            }],
                    ['label' => 'Religion',      'value' => $member->Religion    ?: '—'],
                    ['label' => 'Nationality',   'value' => $member->Nationality ?: '—'],
                    ['label' => 'Profession',    'value' => $member->Profession  ?: '—'],
                    ['label' => 'NID',           'value' => $member->NID         ?: '—'],
                    ['label' => 'Passport',      'value' => $member->PassportNo  ?: '—'],
                ]
            ])

            {{-- Membership Details --}}
            @include('pages.partials.profile-card', [
                'icon'  => 'card_membership',
                'title' => 'Membership Details',
                'rows'  => [
                    ['label' => 'Member ID',   'value' => $member->PrvCusID],
                    ['label' => 'Category',    'value' => $member->MemberCategory ?: '—'],
                    ['label' => 'Status',      'value' => $member->MemExpTypeName ?: '—'],
                    ['label' => 'Join Date',   'value' => $joinDate],
                    ['label' => 'Expiry Date', 'value' => $member->ExpDt
                                                            ? \Carbon\Carbon::parse($member->ExpDt)->format('M d, Y')
                                                            : '—'],
                ]
            ])

            {{-- Contact Info --}}
            <div class="bg-white/10 rounded-xl border border-white/10 overflow-hidden">
                <div class="flex items-center gap-3 p-4 border-b border-white/10">
                    <div class="p-2 bg-primary/10 rounded-lg">
                        <span class="material-symbols-outlined text-primary">contact_page</span>
                    </div>
                    <h3 class="text-white font-bold">Contact Info</h3>
                </div>
                <div class="divide-y divide-white/10">
                    @foreach ([
                        ['icon' => 'phone_iphone', 'label' => 'Mobile',  'value' => $member->Mobile  ?: '—'],
                        ['icon' => 'call',         'label' => 'Phone',   'value' => $member->Phone   ?: '—'],
                        ['icon' => 'mail',         'label' => 'Email',   'value' => $member->Email   ?: '—'],
                        ['icon' => 'location_on',  'label' => 'Address', 'value' => $member->Address ?: '—'],
                    ] as $row)
                        <div class="flex items-start gap-3 px-4 py-3">
                            <span
                                class="material-symbols-outlined text-white/30 text-lg mt-0.5">{{ $row['icon'] }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-white/40 text-[10px] font-bold uppercase tracking-widest">{{ $row['label'] }}</p>
                                <p class="text-white text-sm font-medium break-words">{{ $row['value'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Family Info --}}
            <div class="bg-white/10 rounded-xl border border-white/10 overflow-hidden">
                <div class="flex items-center gap-3 p-4 border-b border-white/10">
                    <div class="p-2 bg-primary/10 rounded-lg">
                        <span class="material-symbols-outlined text-primary">family_restroom</span>
                    </div>
                    <h3 class="text-white font-bold">Family Information</h3>
                </div>
                <div class="divide-y divide-white/10">

                    @include('components.profile-row', ['label' => 'Marital Status', 'value' => $isMarried ? 'Married' : ($member->MaritalStatus ?: '—')])
                    @include('components.profile-row', ['label' => "Father's Name",  'value' => $member->FatherName ?: '—'])
                    @include('components.profile-row', ['label' => "Mother's Name",  'value' => $member->MotherName ?: '—'])

                    @if ($isMarried)
                        @include('components.profile-row', ['label' => 'Spouse Name',    'value' => $member->SpouseName ?: '—'])
                        @include('components.profile-row', ['label' => 'Spouse Blood',   'value' => $member->SpoBlood   ?: '—'])
                        @include('components.profile-row', ['label' => 'Spouse Mobile',  'value' => $member->SpoMobile  ?: '—'])
                        @include('components.profile-row', ['label' => 'Anniversary',    'value' => $weddingDt])
                    @endif

                    @if ($member->NoChild > 0)
                        <div class="px-4 py-3">
                            <p class="text-white/40 text-[10px] font-bold uppercase tracking-widest mb-2">Children
                                ({{ $member->NoChild }})</p>
                            @foreach (['Child1', 'Child2', 'Child3'] as $col)
                                @if ($member->$col)
                                    <div class="flex items-center gap-2 mb-1">
                                        <span
                                            class="material-symbols-outlined text-white/20 text-base">child_care</span>
                                        <p class="text-white text-sm">{{ $member->$col }}</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="grid grid-cols-2 gap-4 pb-4">
                <button
                    class="flex flex-col items-center justify-center p-4 bg-brand-blue rounded-xl shadow-lg border-b-4 border-primary/40 active:scale-95 transition-transform">
                    <span class="material-symbols-outlined text-primary mb-2 text-3xl">qr_code_2</span>
                    <span class="text-white text-xs font-bold uppercase tracking-tight">Show ID Card</span>
                </button>
                <a href="{{ route('ledger') }}"
                   class="flex flex-col items-center justify-center p-4 bg-white/10 rounded-xl border border-white/10 active:scale-95 transition-transform">
                    <span class="material-symbols-outlined text-white/60 mb-2 text-3xl">receipt_long</span>
                    <span class="text-white text-xs font-bold uppercase tracking-tight">Billing History</span>
                </a>
            </div>

        </div>
    </div>
@endsection
