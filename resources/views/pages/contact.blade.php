@extends('layouts.userpanel')
@section('page_title', 'Contact')
@section('show_nav', true)
@section('userpanel_content')

    <div class="min-h-screen pb-24">
        <main class="space-y-5 px-4 py-5">
            <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.12),_transparent_40%),linear-gradient(135deg,rgba(255,255,255,0.08),rgba(255,255,255,0.03))] p-5 shadow-[0_22px_60px_rgba(7,15,25,0.28)]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-white/55">Directory</p>
                        <h2 class="mt-2 font-display text-2xl font-bold text-white">Contact Information</h2>
                        <p class="mt-2 max-w-md text-sm leading-6 text-white/70">All published contact lines are loaded from the live directory and grouped by department.</p>
                    </div>
                    <div class="flex size-14 items-center justify-center rounded-2xl border border-white/10 bg-white/10">
                        <span class="material-symbols-outlined text-3xl text-primary">support_agent</span>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-3 gap-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-3 py-3 text-center">
                        <p class="text-[10px] uppercase tracking-[0.18em] text-white/55">Departments</p>
                        <p class="mt-1 text-lg font-bold text-white">{{ $stats['departments'] }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-3 py-3 text-center">
                        <p class="text-[10px] uppercase tracking-[0.18em] text-white/55">Lines</p>
                        <p class="mt-1 text-lg font-bold text-white">{{ $stats['lines'] }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-3 py-3 text-center">
                        <p class="text-[10px] uppercase tracking-[0.18em] text-white/55">Emails</p>
                        <p class="mt-1 text-lg font-bold text-white">{{ $stats['emails'] }}</p>
                    </div>
                </div>
            </section>

            <a href="{{ 'https://maps.google.com/?q=' . urlencode($companyAddressMapQuery) }}" target="_blank" class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl border border-primary/20 bg-primary/10">
                    <span class="material-symbols-outlined text-xl text-primary">location_on</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="mb-1 text-[10px] font-bold uppercase tracking-wider text-white/55">Address</p>
                    <p class="text-sm font-semibold leading-relaxed text-white/80">
                        {{ $companyName }}@if ($companyAddressLines)<br>@endif
                        @foreach ($companyAddressLines as $line)
                            {{ $line }}@if (! $loop->last)<br>@endif
                        @endforeach
                    </p>
                </div>
                <span class="material-symbols-outlined mt-1 shrink-0 text-white/45">open_in_new</span>
            </a>

            @forelse ($groups as $group)
                <section class="overflow-hidden rounded-[1.65rem] border border-white/10 bg-white/5 shadow-[0_18px_45px_rgba(4,10,20,0.18)]">
                    <div class="flex items-center justify-between gap-4 border-b border-white/10 bg-white/5 px-4 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="flex size-10 items-center justify-center rounded-xl border border-primary/20 bg-primary/10">
                                <span class="material-symbols-outlined text-primary">contacts</span>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-white">{{ $group['department'] }}</p>
                                <p class="text-[10px] uppercase tracking-[0.18em] text-white/55">{{ $group['total_entries'] }} contact line{{ $group['total_entries'] > 1 ? 's' : '' }}</p>
                            </div>
                        </div>

                        <div class="text-right text-[10px] text-white/60">
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
                            <div class="rounded-2xl p-3.5">
                                @if ($subgroup['name'])
                                    <div class="mb-3 flex items-center justify-between gap-1">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-white/60">{{ $subgroup['name'] }}</p>
                                        <span class="h-px flex-1 bg-white/10"></span>
                                    </div>
                                @endif

                                <div class="space-y-2.5">
                                    @foreach ($subgroup['entries'] as $entry)
                                        <div class="grid gap-2 sm:grid-cols-1">
                                            @if ($entry['phone'])
                                                <a href="{{ $entry['phone_href'] }}" class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/5 px-3 py-3 transition active:scale-[0.99]">
                                                    <span class="material-symbols-outlined mt-0.5 text-primary">call</span>
                                                    <div class="min-w-0">
                                                        <p class="text-[10px] uppercase tracking-[0.18em] text-white/55">Phone</p>
                                                        <p class="whitespace-normal break-words text-[15px] font-semibold leading-5 text-white/80">{{ $entry['phone'] }}</p>
                                                    </div>
                                                </a>
                                            @endif

                                            @if ($entry['email'])
                                                <a href="{{ $entry['email_href'] }}" class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/5 px-3 py-3 transition active:scale-[0.99]">
                                                    <span class="material-symbols-outlined mt-0.5 text-primary">mail</span>
                                                    <div class="min-w-0">
                                                        <p class="text-[10px] uppercase tracking-[0.18em] text-white/55">Email</p>
                                                        <p class="whitespace-normal break-all text-[15px] font-semibold leading-5 text-white/80">{{ $entry['email'] }}</p>
                                                    </div>
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @empty
                <section class="rounded-[1.65rem] border border-dashed border-white/15 bg-white/[0.03] px-5 py-12 text-center">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-2xl border border-white/10 bg-white/5">
                        <span class="material-symbols-outlined text-3xl text-primary">contact_support</span>
                    </div>
                    <h3 class="mt-4 text-base font-bold text-white">No contact information available</h3>
                    <p class="mt-2 text-sm leading-6 text-white/65">The contact directory is empty right now. Add records from the admin panel and they will appear here automatically.</p>
                </section>
            @endforelse
        </main>
    </div>

@endsection
