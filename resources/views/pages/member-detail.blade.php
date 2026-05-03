@extends('layouts.userpanel')
@section('page_title', 'Member Detail')
@section('show_nav', true)

@section('userpanel_content')
<div x-data="{ previewOpen: false }"
     x-on:keydown.escape.window="previewOpen = false"
     class="flex flex-col min-h-screen pb-24">

    <div class="bg-primary/5 w-full pt-4 pb-14 px-4 rounded-b-[2.5rem] shadow-2xl">

        <div class="flex flex-col items-center">
            <div class="relative">
                <button type="button"
                        @if ($profilePhotoUrl) x-on:click="previewOpen = true" @endif
                        class="relative rounded-full h-28 w-28 mb-4 overflow-hidden flex items-center justify-center bg-brand-blue/80"
                        :class="{ 'active:scale-95 transition-transform': {{ $profilePhotoUrl ? 'true' : 'false' }} }"
                        style="border: 4px solid var(--member-primary); box-shadow: 0 0 15px var(--member-primary-glow);"
                        aria-label="Preview profile picture">
                    @if ($profilePhotoUrl)
                        <img class="member-avatar-photo rounded-full"
                             src="{{ $profilePhotoUrl }}"
                             alt="{{ $fullName }} profile picture">
                    @else
                        <span class="text-primary font-extrabold text-3xl">{{ $initials }}</span>
                    @endif
                </button>
            </div>
            <div class="text-center mt-3">
                <h1 class="text-white text-2xl font-extrabold tracking-tight">{{ $fullName }}</h1>
                <p class="text-primary font-semibold text-sm tracking-[0.1em] uppercase mt-1">
                    {{ $member->MemberCategory ?? 'Member' }} • {{ $member->PrvCusID }}
                </p>
            </div>
        </div>
    </div>

    <div class="px-4 mt-4 relative z-10 space-y-4">
        <div class="grid grid-cols-2 gap-3">
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

        @php
            $contactActions = [
                ['label' => 'Call', 'icon' => 'call', 'href' => $callHref],
                ['label' => 'SMS', 'icon' => 'sms', 'href' => $smsHref],
                ['label' => 'Email', 'icon' => 'mail', 'href' => $emailHref],
            ];
        @endphp

        <div class="grid grid-cols-3 gap-3">
            @foreach ($contactActions as $action)
                @if ($action['href'])
                    <a href="{{ $action['href'] }}"
                       class="flex flex-col items-center justify-center rounded-2xl border border-primary/25 bg-primary/10 px-3 py-4 text-center active:scale-95 transition-transform">
                        <span class="material-symbols-outlined text-primary text-2xl">{{ $action['icon'] }}</span>
                        <span class="mt-2 text-[11px] font-bold uppercase tracking-[0.18em] text-white">{{ $action['label'] }}</span>
                    </a>
                @else
                    <div class="flex flex-col items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-3 py-4 text-center opacity-40">
                        <span class="material-symbols-outlined text-white text-2xl">{{ $action['icon'] }}</span>
                        <span class="mt-2 text-[11px] font-bold uppercase tracking-[0.18em] text-white">{{ $action['label'] }}</span>
                    </div>
                @endif
            @endforeach
        </div>

        <x-profile-card icon="person" title="Personal Information">
            <x-profile-row label="Full Name" :value="$fullName" />
            <x-profile-row label="Date of Birth" :value="$birthDate . ($age !== '—' ? ' (' . $age . ')' : '')" />
            <x-profile-row label="Blood Group" :value="$member->BloodGroup ?: '—'" />
            <x-profile-row label="Gender" :value="match(strtolower($member->Sex ?? '')) {
                'm' => 'Male',
                'f' => 'Female',
                default => $member->Sex ?: '—',
            }" />
            <x-profile-row label="Religion" :value="$member->Religion ?: '—'" />
            <x-profile-row label="Nationality" :value="$member->Nationality ?: '—'" />
            <x-profile-row label="Profession" :value="$member->Profession ?: '—'" />
            <x-profile-row label="Company Name" :value="data_get($member, 'ComName') ?: '—'" />
        </x-profile-card>

        <x-profile-card icon="card_membership" title="Membership Details">
            <x-profile-row label="Member ID" :value="$member->PrvCusID" />
            <x-profile-row label="Category" :value="$member->MemberCategory ?: '—'" />
            <x-profile-row label="Status" :value="$member->MemExpTypeName ?: '—'" />
            <x-profile-row label="Join Date" :value="$joinDate" />
            <x-profile-row label="Expiry Date" :value="$member->ExpDt ? \Carbon\Carbon::parse($member->ExpDt)->format('M d, Y') : '—'" />
        </x-profile-card>

        <div x-data="{ open: false }" class="bg-white/10 rounded-xl border border-white/10 overflow-hidden">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between p-4 active:bg-white/5 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary/10 rounded-lg flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary">contact_page</span>
                    </div>
                    <h3 class="text-white font-bold">Contact Info</h3>
                </div>
                <span class="material-symbols-outlined text-white/40 transition-transform duration-300"
                      :class="open ? 'rotate-180' : ''">expand_more</span>
            </button>
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="divide-y divide-white/10 border-t border-white/10">
                @foreach ([
                    ['icon' => 'phone_iphone', 'label' => 'Mobile', 'value' => $member->Mobile ?: '—'],
                    ['icon' => 'call', 'label' => 'Phone', 'value' => $member->Phone ?: '—'],
                    ['icon' => 'mail', 'label' => 'Email', 'value' => $member->Email ?: '—'],
                    ['icon' => 'location_on', 'label' => 'Address', 'value' => $member->Address ?: '—'],
                ] as $row)
                    <div class="flex items-start gap-3 px-4 py-3">
                        <span class="material-symbols-outlined text-white/30 text-lg mt-0.5">{{ $row['icon'] }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-white/40 text-[10px] font-bold uppercase tracking-widest">{{ $row['label'] }}</p>
                            <p class="text-white text-sm font-medium break-words">{{ $row['value'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div x-data="{ open: false }" class="bg-white/10 rounded-xl border border-white/10 overflow-hidden">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between p-4 active:bg-white/5 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary/10 rounded-lg flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary">family_restroom</span>
                    </div>
                    <h3 class="text-white font-bold">Family Information</h3>
                </div>
                <span class="material-symbols-outlined text-white/40 transition-transform duration-300"
                      :class="open ? 'rotate-180' : ''">expand_more</span>
            </button>
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="divide-y divide-white/10 border-t border-white/10">
                <x-profile-row label="Marital Status" :value="$isMarried ? 'Married' : ($member->MaritalStatus ?: '—')" />
                <x-profile-row label="Father's Name" :value="$member->FatherName ?: '—'" />
                <x-profile-row label="Mother's Name" :value="$member->MotherName ?: '—'" />

                @if ($isMarried)
                    <x-profile-row label="Spouse Name" :value="$member->SpouseName ?: '—'" />
                    <x-profile-row label="Spouse Blood" :value="$member->SpoBlood ?: '—'" />
                    <x-profile-row label="Spouse Mobile" :value="$member->SpoMobile ?: '—'" />
                    <x-profile-row label="Anniversary" :value="$weddingDt" />
                @endif

                @php
                    $childrenList = collect($children)
                        ->pluck('name')
                        ->filter()
                        ->values();
                @endphp

                @if ($childrenList->isNotEmpty())
                    <div class="px-4 py-3">
                        <p class="mb-2 text-white/40 text-[10px] font-bold uppercase tracking-widest">
                            Children ({{ max($childrenCount, $childrenList->count()) }})
                        </p>
                        @foreach ($childrenList as $child)
                            <div class="mb-1 flex items-center gap-2">
                                <span class="material-symbols-outlined text-white/20 text-base">child_care</span>
                                <p class="text-white text-sm">{{ $child }}</p>
                            </div>
                        @endforeach
                    </div>
                @elseif ($isMarried)
                    <div class="px-4 py-3">
                        <p class="text-sm text-white/55">No children information is added.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($profilePhotoUrl)
        <div x-show="previewOpen"
             x-transition.opacity
             class="fixed inset-0 z-[80] flex items-center justify-center p-4"
             style="display: none;">
            <button type="button"
                    x-on:click="previewOpen = false"
                    class="absolute inset-0 bg-slate-950/35"
                    aria-label="Close image preview"></button>

            <div class="relative w-full max-w-sm">
                <button type="button"
                        x-on:click="previewOpen = false"
                        class="absolute right-4 top-4 z-10 flex size-10 items-center justify-center rounded-full border border-white/10 bg-slate-950/25 text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>

                <div class="member-modal-surface rounded-[2rem] border border-white/10 p-4 shadow-2xl">
                    <div class="aspect-[4/5] overflow-hidden rounded-[1.5rem] border border-primary/20 bg-white/5">
                        <img src="{{ $profilePhotoUrl }}"
                             alt="{{ $fullName }} full-size profile picture"
                             class="member-avatar-photo member-avatar-photo-preview">
                    </div>

                    <div class="pt-4 text-center">
                        <p class="text-white text-base font-bold">{{ $fullName }}</p>
                        <p class="mt-1 text-xs text-white/40">Member ID: {{ $member->PrvCusID }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
