@extends('layouts.admin')

@section('page_title', 'Affiliated Clubs')
@section('page_eyebrow', 'Directory')

@section('content')
<div class="space-y-4">
    <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[10px] uppercase tracking-[0.24em] text-white/35">Club Directory</p>
                <h2 class="mt-1 font-display text-xl font-bold text-white">Manage Affiliated Clubs</h2>
                <p class="mt-1 text-xs text-white/45">Create, edit, and remove affiliated club entries, including uploaded images stored under `public/affiliated_clubs`.</p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <form method="GET" action="{{ route('admin.affiliated-clubs.index') }}" class="flex min-w-[18rem] items-center gap-2 border border-[#30384a] bg-slate-950/20 px-3 py-2">
                    <span class="material-symbols-outlined text-[18px] text-white/35">search</span>
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Search club, address, contact, phone"
                        class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-white placeholder:text-white/25 focus:ring-0"
                    >
                </form>

                <div class="flex gap-2">
                    @if ($search !== '')
                        <a href="{{ route('admin.affiliated-clubs.index') }}" class="inline-flex h-10 items-center justify-center border border-[#30384a] px-4 text-sm text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                            Clear
                        </a>
                    @endif

                    <a href="{{ route('admin.affiliated-clubs.create') }}" class="inline-flex h-10 items-center justify-center border border-[#30384a] bg-admin-gold px-4 text-sm font-medium text-admin-ink">
                        Add Club
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-admin-line/10 bg-white/[0.03] shadow-panel">
        <div class="flex items-center justify-between border-b border-admin-line/10 px-4 py-3">
            <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">Club List</p>
            <p class="text-xs text-white/45">{{ $clubs->total() }} records</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-admin-line/10 bg-slate-950/20 text-[10px] uppercase tracking-[0.2em] text-white/35">
                    <tr>
                        <th class="px-4 py-3 font-medium">Club</th>
                        <th class="px-4 py-3 font-medium">Branch</th>
                        <th class="px-4 py-3 font-medium">Contact</th>
                        <th class="px-4 py-3 font-medium">Order</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-admin-line/10">
                    @forelse ($clubs as $club)
                        @php
                            $words = preg_split('/\s+/', trim($club->display_name)) ?: [];
                            $initials = collect(array_values(array_filter($words)))
                                ->take(2)
                                ->map(fn (string $word) => strtoupper(substr($word, 0, 1)))
                                ->join('');
                        @endphp
                        <tr class="align-top">
                            <td class="px-4 py-3.5">
                                <div class="flex items-start gap-3">
                                    <div class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-admin-line/10 bg-slate-950/20">
                                        @if ($club->display_logo_url)
                                            <img src="{{ $club->display_logo_url }}" alt="{{ $club->display_name }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="font-display text-sm font-bold text-admin-gold">{{ $initials !== '' ? $initials : 'AC' }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-white">{{ $club->display_name }}</p>
                                        <p class="mt-1 text-xs text-white/45">{{ $club->display_address ?? 'Address not set' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-white/65">{{ $club->branch_name_label ?? 'Head Office' }}</td>
                            <td class="px-4 py-3.5">
                                <div class="space-y-1 text-xs text-white/65">
                                    <p>{{ trim((string) $club->tx_mobile) ?: trim((string) $club->BranchTel) ?: trim((string) $club->HOTel) ?: 'No phone' }}</p>
                                    <p>{{ trim((string) $club->tx_email) ?: 'No email' }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-white/65">#{{ $club->id_serial ?? 'N/A' }}</td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] {{ $club->is_active ? 'border-emerald-400/20 bg-emerald-500/10 text-emerald-300' : 'border-amber-400/20 bg-amber-500/10 text-amber-300' }}">
                                    {{ $club->is_active ? 'Active' : 'Hidden' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('admin.affiliated-clubs.edit', $club->id_affiliated_club_key) }}" class="inline-flex h-8 items-center border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.affiliated-clubs.destroy', $club->id_affiliated_club_key) }}" onsubmit="return confirm('Delete this affiliated club?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex h-8 items-center border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-white/45">
                                {{ $search !== '' ? 'No affiliated clubs matched the current search.' : 'No affiliated clubs found.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3">
            @include('admin.partials.pagination', ['paginator' => $clubs])
        </div>
    </section>
</div>
@endsection
