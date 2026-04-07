<div x-data="{ previewOpen: false }"
     x-on:keydown.escape.window="previewOpen = false"
     class="flex flex-col min-h-screen pb-24">

    {{-- ── Blue Header ───────────────────────────────────── --}}
    <div class="bg-brand-blue w-full pt-4 pb-14 px-4 rounded-b-[2.5rem] shadow-2xl">

        {{-- App Bar --}}
        <div class="flex items-center justify-between mb-8">
            <a href="{{ route('dashboard') }}"
               class="text-white flex size-10 items-center justify-center rounded-full bg-white/10 ios-blur">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-white text-lg font-bold tracking-tight">Member Profile</h2>
            <div class="size-10"></div>
        </div>

        {{-- Avatar (initials) --}}
        <div class="flex flex-col items-center">
            <div class="relative">
                <button type="button"
                        @if ($hasProfilePhoto) x-on:click="previewOpen = true" @endif
                        class="relative rounded-full h-28 w-28 mb-4 overflow-hidden flex items-center justify-center bg-brand-blue/80"
                        :class="{ 'active:scale-95 transition-transform': {{ $hasProfilePhoto ? 'true' : 'false' }} }"
                        style="border: 4px solid #f2d00d; box-shadow: 0 0 15px rgba(242,208,13,0.3);"
                        aria-label="Preview profile picture">
                    @if ($hasProfilePhoto)
                        <img class="size-full rounded-full object-cover object-top"
                             src="{{ $profilePhotoUrl }}"
                             alt="{{ $fullName }} profile picture">
                        <span class="absolute inset-x-0 bottom-0 flex justify-center bg-slate-950/20 py-1 backdrop-blur-sm">
                            <span class="material-symbols-outlined text-sm text-white">zoom_in</span>
                        </span>
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

    {{-- ── Body ───────────────────────────────────────────── --}}
    <div class="px-4 mt-4 relative z-10 space-y-4">

        {{-- Quick stat chips --}}
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

        {{-- ── Personal Info ─────────────────────────────── --}}
        <x-profile-card icon="person" title="Personal Information">
            <x-profile-row label="Full Name"     :value="$fullName" />
            <x-profile-row label="Date of Birth" :value="$birthDate . ($age !== '—' ? ' ('.$age.')' : '')" />
            <x-profile-row label="Blood Group"   :value="$member->BloodGroup  ?: '—'" />
            <x-profile-row label="Gender"        :value="match(strtolower($member->Sex ?? '')) {
                                                            'm' => 'Male', 'f' => 'Female', default => $member->Sex ?: '—'
                                                          }" />
            <x-profile-row label="Religion"      :value="$member->Religion    ?: '—'" />
            <x-profile-row label="Nationality"   :value="$member->Nationality ?: '—'" />
            <x-profile-row label="Profession"    :value="$member->Profession  ?: '—'" />
            <x-profile-row label="NID"           :value="$member->NID         ?: '—'" />
            <x-profile-row label="Passport"      :value="$member->PassportNo  ?: '—'" />
        </x-profile-card>

        {{-- ── Membership Details ───────────────────────── --}}
        <x-profile-card icon="card_membership" title="Membership Details">
            <x-profile-row label="Member ID"   :value="$member->PrvCusID" />
            <x-profile-row label="Category"    :value="$member->MemberCategory ?: '—'" />
            <x-profile-row label="Status"      :value="$member->MemExpTypeName ?: '—'" />
            <x-profile-row label="Join Date"   :value="$joinDate" />
        </x-profile-card>

        {{-- ── Contact Info ─────────────────────────────── --}}
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
                    ['icon' => 'phone_iphone', 'label' => 'Mobile',  'value' => $member->Mobile  ?: '—'],
                    ['icon' => 'call',         'label' => 'Phone',   'value' => $member->Phone   ?: '—'],
                    ['icon' => 'mail',         'label' => 'Email',   'value' => $member->Email   ?: '—'],
                    ['icon' => 'location_on',  'label' => 'Address', 'value' => $member->Address ?: '—'],
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

        {{-- ── Family Info ───────────────────────────────── --}}
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
                <x-profile-row label="Father's Name"  :value="$member->FatherName ?: '—'" />
                <x-profile-row label="Mother's Name"  :value="$member->MotherName ?: '—'" />

                @if ($isMarried)
                    <x-profile-row label="Spouse Name"   :value="$member->SpouseName ?: '—'" />
                    <x-profile-row label="Spouse Blood"  :value="$member->SpoBlood   ?: '—'" />
                    <x-profile-row label="Spouse Mobile" :value="$member->SpoMobile  ?: '—'" />
                    <x-profile-row label="Anniversary"   :value="$weddingDt" />
                @endif

                <div class="px-4 py-3">
                    @php
                        $visibleChildrenCount = count($children);
                        $childCountBadge = max($childrenCount, $visibleChildrenCount);
                    @endphp

                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-white/40 text-[10px] font-bold uppercase tracking-widest">Children Information</p>
                            <p class="mt-1 text-white/30 text-xs">Showing the child details stored in the member database record.</p>
                        </div>
                        <span class="shrink-0 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-primary">
                            {{ $childCountBadge }}
                        </span>
                    </div>

                    @if ($visibleChildrenCount > 0)
                        <div class="space-y-3">
                            @foreach ($children as $child)
                                <div class="rounded-2xl border border-white/10 bg-white/5 p-3">
                                    <div class="flex items-start gap-3">
                                        <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary/15 text-sm font-extrabold text-primary">
                                            {{ $child['slot'] }}
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <p class="text-white text-sm font-bold leading-tight">{{ $child['name'] }}</p>

                                            @if (! empty($child['sex']) || ! empty($child['blood']))
                                                <div class="mt-2 flex flex-wrap gap-2">
                                                    @if (! empty($child['sex']))
                                                        <span class="rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-white/70">
                                                            {{ $child['sex'] }}
                                                        </span>
                                                    @endif
                                                    @if (! empty($child['blood']))
                                                        <span class="rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-white/70">
                                                            Blood: {{ $child['blood'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif

                                            <div class="mt-3 space-y-2">
                                                @if (! empty($child['dob']))
                                                    <div class="flex items-center gap-2 text-xs text-white/70">
                                                        <span class="material-symbols-outlined text-base text-primary/80">cake</span>
                                                        <span>{{ $child['dob'] }}</span>
                                                    </div>
                                                @endif

                                                @if (! empty($child['mobile']))
                                                    <div class="flex items-center gap-2 text-xs text-white/70">
                                                        <span class="material-symbols-outlined text-base text-primary/80">call</span>
                                                        <span>{{ $child['mobile'] }}</span>
                                                    </div>
                                                @endif

                                                @if (! empty($child['email']))
                                                    <div class="flex items-center gap-2 text-xs text-white/70 break-all">
                                                        <span class="material-symbols-outlined text-base text-primary/80">mail</span>
                                                        <span>{{ $child['email'] }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif ($isMarried)
                        <div class="rounded-2xl border border-dashed border-white/10 bg-white/5 px-4 py-3">
                            <p class="text-sm text-white/55">No children information is added.</p>
                        </div>
                    @endif

                    @if ($hasMoreChildren || $childrenCount > $visibleChildrenCount)
                        <p class="mt-3 text-[11px] leading-relaxed text-primary/75">
                            The database indicates additional child entries beyond the detailed records currently available in the profile fields.
                        </p>
                    @endif
                </div>

            </div>
        </div>

        {{-- ── Quick Actions ─────────────────────────────── --}}
        <div class="grid grid-cols-2 gap-4 pb-4">
            <button class="flex flex-col items-center justify-center p-4 bg-brand-blue rounded-xl shadow-lg border-b-4 border-primary/40 active:scale-95 transition-transform">
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

    {{-- Bottom Navigation --}}
    @include('layouts.bottom-nav')

    @if ($hasProfilePhoto)
        <div x-show="previewOpen"
             x-transition.opacity
             class="fixed inset-0 z-[80] flex items-center justify-center p-4"
             style="display: none;">
            <button type="button"
                    x-on:click="previewOpen = false"
                    class="absolute inset-0 bg-slate-950/35 backdrop-blur-sm"
                    aria-label="Close image preview"></button>

            <div class="relative w-full max-w-sm">
                <button type="button"
                        x-on:click="previewOpen = false"
                        class="absolute right-4 top-4 z-10 flex size-10 items-center justify-center rounded-full border border-white/10 bg-slate-950/25 text-white backdrop-blur-sm">
                    <span class="material-symbols-outlined">close</span>
                </button>

                <div class="rounded-[2rem] border border-white/10 bg-brand-blue/90 p-4 shadow-2xl">
                    <div class="aspect-[4/5] overflow-hidden rounded-[1.5rem] border border-primary/20 bg-white/5">
                        <img src="{{ $profilePhotoUrl }}"
                             alt="{{ $fullName }} full-size profile picture"
                             class="size-full object-cover object-top">
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
