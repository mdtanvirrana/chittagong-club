@extends('layouts.userpanel')
@section('page_title', 'My Profile')
@section('show_nav', true)

@section('userpanel_content')
<div x-data="{ previewOpen: false, qrModalOpen: false }"
     x-on:keydown.escape.window="previewOpen = false; qrModalOpen = false"
     class="flex flex-col min-h-screen pb-24">

    <div class="bg-primary/5 w-full pt-4 pb-14 px-4 rounded-b-[2.5rem] shadow-2xl">
        <div class="flex flex-col items-center">
            <div class="relative">
                <button type="button"
                        @if ($hasProfilePhoto) x-on:click="previewOpen = true" @endif
                        class="relative rounded-full h-28 w-28 mb-4 overflow-hidden flex items-center justify-center bg-brand-blue/80"
                        :class="{ 'active:scale-95 transition-transform': {{ $hasProfilePhoto ? 'true' : 'false' }} }"
                        style="border: 4px solid var(--member-primary); box-shadow: 0 0 15px var(--member-primary-glow);"
                        aria-label="Preview profile picture">
                    @if ($hasProfilePhoto)
                        <img class="member-avatar-photo rounded-full"
                             src="{{ $profilePhotoUrl }}"
                             alt="{{ $fullName }} profile picture">
                    @else
                        <span class="text-primary font-extrabold text-3xl">{{ $initials }}</span>
                    @endif
                </button>
            </div>
            <div class="mt-3 text-center">
                <h1 class="text-white text-2xl font-extrabold tracking-tight">{{ $fullName }}</h1>
                <p class="mt-1 text-primary font-semibold text-sm tracking-[0.1em] uppercase">
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
            <x-profile-row label="NID" :value="$member->NID ?: '—'" />
            <x-profile-row label="Passport" :value="$member->PassportNo ?: '—'" />
        </x-profile-card>

        <x-profile-card icon="card_membership" title="Membership Details">
            <x-profile-row label="Member ID" :value="$member->PrvCusID" />
            <x-profile-row label="Category" :value="$member->MemberCategory ?: '—'" />
            <x-profile-row label="Status" :value="$member->MemExpTypeName ?: '—'" />
            <x-profile-row label="Join Date" :value="$joinDate" />
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

        <div
            x-data="{ open: {{ $errors->any() || session()->has('password_status') ? 'true' : 'false' }} }"
            class="bg-white/10 rounded-xl border border-white/10 overflow-hidden"
        >
            <button @click="open = !open"
                    class="w-full flex items-center justify-between p-4 active:bg-white/5 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary/10 rounded-lg flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary">lock</span>
                    </div>
                    <div class="text-left">
                        <h3 class="text-white font-bold">Change Password</h3>
                        <p class="mt-1 text-xs text-white/40">Update the password you use to sign in to the member panel.</p>
                    </div>
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
                 class="border-t border-white/10 px-4 py-4"
                 style="display: none;">
                @if (session('password_status'))
                    <div class="mb-4 rounded-2xl border border-primary/20 bg-primary/10 px-4 py-3 text-sm font-medium text-primary">
                        {{ session('password_status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-2xl border border-amber-400/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="current_password" class="mb-2 block text-sm font-semibold text-white/75">Current Password</label>
                        <input
                            id="current_password"
                            name="current_password"
                            type="password"
                            autocomplete="current-password"
                            class="w-full rounded-2xl border px-4 py-3 text-sm text-white placeholder:text-white/25 focus:outline-none focus:ring-2 focus:ring-primary/30 {{ $errors->has('current_password') ? 'border-red-400/50 bg-red-500/10' : 'border-white/10 bg-white/[0.04]' }}"
                            placeholder=""
                            required
                        >
                    </div>

                    <div>
                        <label for="new_password" class="mb-2 block text-sm font-semibold text-white/75">New Password</label>
                        <input
                            id="new_password"
                            name="new_password"
                            type="password"
                            autocomplete="new-password"
                            class="w-full rounded-2xl border px-4 py-3 text-sm text-white placeholder:text-white/25 focus:outline-none focus:ring-2 focus:ring-primary/30 {{ $errors->has('new_password') ? 'border-red-400/50 bg-red-500/10' : 'border-white/10 bg-white/[0.04]' }}"
                            required
                        >
                    </div>

                    <div>
                        <label for="new_password_confirmation" class="mb-2 block text-sm font-semibold text-white/75">Confirm New Password</label>
                        <input
                            id="new_password_confirmation"
                            name="new_password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            class="w-full rounded-2xl border px-4 py-3 text-sm text-white placeholder:text-white/25 focus:outline-none focus:ring-2 focus:ring-primary/30 {{ $errors->has('new_password') ? 'border-red-400/50 bg-red-500/10' : 'border-white/10 bg-white/[0.04]' }}"
                            required
                        >
                    </div>

                    <div class="flex items-center justify-between gap-4 pt-1">
                        <p class="text-xs leading-relaxed text-white/40">
                            Your next sign-in will use the new password.
                        </p>

                        <button
                            type="submit"
                            class="inline-flex shrink-0 items-center justify-center rounded-full bg-primary px-5 py-2.5 text-sm font-bold text-brand-blue shadow-lg shadow-primary/20 transition-transform active:scale-95"
                        >
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 pb-4">
            <a href="{{ route('ledger') }}"
               class="flex min-h-[11.5rem] flex-col items-center justify-center rounded-xl border border-white/10 bg-white/10 p-4 text-center active:scale-95 transition-transform">
                <span class="material-symbols-outlined text-white/60 mb-2 text-3xl">receipt_long</span>
                <span class="text-white text-xs font-bold uppercase tracking-tight">Billing History</span>
            </a>

            <button
                type="button"
                x-on:click="qrModalOpen = true; requestAnimationFrame(() => window.renderMemberProfileQr && window.renderMemberProfileQr())"
                class="flex min-h-[11.5rem] flex-col items-center justify-center rounded-xl border border-white/10 bg-white/10 p-4 text-center transition-transform active:scale-95"
            >
                <div class="flex size-14 items-center justify-center rounded-full border border-primary/20 bg-primary/10">
                    <span class="material-symbols-outlined text-primary text-[1.9rem]">qr_code_2</span>
                </div>
                <span class="mt-3 text-white text-xs font-bold uppercase tracking-tight">Member Info QR</span>
            </button>
        </div>
    </div>

    @if ($hasProfilePhoto)
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

    <div
        x-show="qrModalOpen"
        x-transition.opacity
        class="fixed inset-0 z-[85] flex items-center justify-center p-4"
        style="display: none;"
    >
        <button
            type="button"
            x-on:click="qrModalOpen = false"
            class="absolute inset-0 bg-slate-950/45 backdrop-blur-sm"
            aria-label="Close member info QR modal"
        ></button>

        <div class="relative w-full max-w-sm">
            <button
                type="button"
                x-on:click="qrModalOpen = false"
                class="absolute right-4 top-4 z-10 flex size-10 items-center justify-center rounded-full border border-white/10 bg-slate-950/25 text-white"
            >
                <span class="material-symbols-outlined">close</span>
            </button>

            <div class="member-modal-surface overflow-hidden rounded-[2rem] border border-white/10 shadow-2xl">
                <div class="bg-primary/10 px-6 pb-5 pt-6 text-center">
                    <div class="mx-auto flex size-16 items-center justify-center rounded-full border border-primary/20 bg-primary/10">
                        <span class="material-symbols-outlined text-primary text-[2rem]">qr_code_2</span>
                    </div>
                    <p class="mt-4 text-[11px] font-semibold uppercase tracking-[0.22em] text-primary">Member Profile</p>
                    <h3 class="mt-2 text-xl font-extrabold text-white">Member Info QR</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">
                        Scan to read the member name and membership ID.
                    </p>
                </div>

                <div class="px-6 py-6 ">
                    <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5 shadow-inner shadow-slate-900/5">
                        <div
                            data-member-qr-value='@json($memberQrValue)'
                            id="member-profile-qr-code"
                            class="mx-auto flex size-56 items-center justify-center overflow-hidden rounded-[1.5rem] bg-white p-4"
                        >
                            <span class="text-xs font-medium text-black">Generating QR...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.renderMemberProfileQr = function () {
                var container = document.getElementById('member-profile-qr-code');

                if (!container || container.dataset.qrRendered === 'true') {
                    return;
                }

                var qrValue = container.dataset.memberQrValue || '';

                try {
                    qrValue = JSON.parse(qrValue);
                } catch (error) {
                    // Keep the raw value if it is not JSON encoded.
                }

                if (!qrValue || typeof window.QRCode === 'undefined') {
                    container.innerHTML = '<span class="text-xs font-medium text-black">QR unavailable</span>';
                    return;
                }

                container.innerHTML = '';

                new window.QRCode(container, {
                    text: qrValue,
                    width: 176,
                    height: 176,
                    colorDark: '#7a0f22',
                    colorLight: '#ffffff',
                    correctLevel: window.QRCode.CorrectLevel.M
                });

                var renderedCode = container.querySelector('img, canvas');

                if (renderedCode) {
                    renderedCode.style.width = '100%';
                    renderedCode.style.height = '100%';
                    renderedCode.style.display = 'block';
                    renderedCode.style.borderRadius = '1rem';
                }

                container.dataset.qrRendered = 'true';
            };
        });
    </script>
</div>
@endsection
