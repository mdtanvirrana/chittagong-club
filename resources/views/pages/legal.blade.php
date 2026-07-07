@php use App\Support\MemberSession; @endphp
@extends('layouts.app')
@section('page_title', $pageTitle)

@php
    $homeRoute = session()->has(MemberSession::KEY) ? route('dashboard') : route('login');
    $homeLabel = session()->has(MemberSession::KEY) ? 'Back to Dashboard' : 'Back to Sign In';
@endphp

@section('content')
    <div class="min-h-screen px-4 py-5">
        <main class="space-y-5">
            <section
                class="overflow-hidden rounded-[2rem] bg-brand-blue px-5 py-5 text-white shadow-[0_24px_65px_rgba(127,29,29,0.28)]">

                <!-- Top row -->
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-white/60">
                            Public Information
                        </p>

                        <h1 class="mt-2 text-2xl font-bold leading-tight text-white">
                            {{ $pageTitle }}
                        </h1>
                    </div>

                    <a href="{{ $homeRoute }}"
                       class="inline-flex shrink-0 items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-white/80 transition hover:bg-white/15">
                        <span class="material-symbols-outlined text-base">arrow_back</span>
                        {{ $homeLabel }}
                    </a>
                </div>

                <!-- ✅ Full width description -->
                <p class="mt-3 text-sm leading-6 text-white/75">
                    {{ $pageDescription }}
                </p>

                <!-- Company block -->
                <div class="mt-5 flex items-center gap-3 rounded-[1.6rem] border border-white/10 bg-white/10 px-4 py-3">
                    <img src="{{ $companyLogoUrl }}" alt="{{ $companyName }}"
                         class="company-name-canterbury size-11 rounded-full border border-white/15 bg-white object-contain p-1.5"/>

                    <div>
                        <p class="text-xl font-semibold text-white company-name-canterbury">
                            {{ $companyName }}
                        </p>
                    </div>
                </div>

            </section>
            @foreach ($sections as $section)
                <section
                    class="rounded-[1.75rem] border border-primary/10 bg-white/90 px-5 py-5 shadow-[0_20px_48px_rgba(127,29,29,0.07)]">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                            <span class="material-symbols-outlined text-[22px]">{{ $section['icon'] }}</span>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-lg font-bold text-black">{{ $section['title'] }}</h2>
                            <div class="mt-3 space-y-3 text-sm leading-6 text-black">
                                @foreach ($section['body'] as $paragraph)
                                    <p>{{ $paragraph }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
            @endforeach

            @if ($pageKey === 'contact')
                <section class="space-y-4 pb-4">
                    @forelse ($contactGroups as $group)
                        <div
                            class="overflow-hidden rounded-[1.65rem] border border-primary/10 bg-white/85 shadow-[0_18px_45px_rgba(127,29,29,0.06)]">
                            <div
                                class="flex items-center justify-between gap-4 border-b border-primary/10 bg-primary/5 px-4 py-3.5">
                                <div>
                                    <p class="text-base font-bold text-black">{{ $group['department'] }}</p>
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-primary/65">
                                        {{ $group['total_entries'] }} contact
                                        line{{ $group['total_entries'] > 1 ? 's' : '' }}
                                    </p>
                                </div>

                                <div class="text-right text-[11px] leading-5 text-black">
                                    @if ($group['phone_count'] > 0)
                                        <p>{{ $group['phone_count'] }} phone</p>
                                    @endif
                                    @if ($group['email_count'] > 0)
                                        <p>{{ $group['email_count'] }} email</p>
                                    @endif
                                </div>
                            </div>

                            <div class="space-y-3 p-4">
                                @foreach ($group['subgroups'] as $subgroup)
                                    <div class="rounded-2xl bg-primary/5 p-3.5">
                                        @if ($subgroup['name'])
                                            <p class="mb-3 text-[11px] font-bold uppercase tracking-[0.18em] text-primary/70">{{ $subgroup['name'] }}</p>
                                        @endif

                                        <div class="space-y-2.5">
                                            @foreach ($subgroup['entries'] as $entry)
                                                @if ($entry['phone'])
                                                    <a href="{{ $entry['phone_href'] }}"
                                                       class="flex items-start gap-3 rounded-2xl border border-primary/10 bg-white px-3 py-3 transition hover:bg-primary/5">
                                                        <span
                                                            class="material-symbols-outlined mt-0.5 text-primary">call</span>
                                                        <div class="min-w-0">
                                                            <p class="text-[10px] uppercase tracking-[0.18em] text-black">
                                                                Phone</p>
                                                            <p class="break-words text-[15px] font-semibold leading-5 text-black">{{ $entry['phone'] }}</p>
                                                        </div>
                                                    </a>
                                                @endif

                                                @if ($entry['email'])
                                                    <a href="{{ $entry['email_href'] }}"
                                                       class="flex items-start gap-3 rounded-2xl border border-primary/10 bg-white px-3 py-3 transition hover:bg-primary/5">
                                                        <span
                                                            class="material-symbols-outlined mt-0.5 text-primary">mail</span>
                                                        <div class="min-w-0">
                                                            <p class="text-[10px] uppercase tracking-[0.18em] text-black">
                                                                Email</p>
                                                            <p class="break-all text-[15px] font-semibold leading-5 text-black">{{ $entry['email'] }}</p>
                                                        </div>
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div
                            class="rounded-[1.65rem] border border-dashed border-primary/20 bg-white/80 px-5 py-10 text-center shadow-[0_16px_40px_rgba(127,29,29,0.05)]">
                            <div
                                class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                                <span class="material-symbols-outlined text-3xl">contact_support</span>
                            </div>
                            <h3 class="mt-4 text-base font-bold text-black">No contact information available</h3>
                            <p class="mt-2 text-sm leading-6 text-black">The public contact directory is empty right
                                now. Once contacts are published from the admin panel, they will appear here
                                automatically.</p>
                        </div>
                    @endforelse
                </section>
            @endif
        </main>
    </div>
@endsection
