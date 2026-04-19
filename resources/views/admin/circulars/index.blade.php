@extends('layouts.admin')

@section('page_title', 'Circulars')
@section('page_eyebrow', 'Content')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-[2rem] border border-white/8 bg-white/[0.04] p-6 shadow-panel lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="font-display text-3xl font-bold text-white">Manage member circulars</h2>
            <p class="mt-2 text-sm leading-7 text-white/60">Create, schedule, publish, or archive circulars stored in `T_CAREER`.</p>
        </div>
        <a href="{{ route('admin.circulars.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-admin-gold px-5 py-3 font-semibold text-admin-ink">
            Create Circular
        </a>
    </div>

    <div class="overflow-hidden rounded-[2rem] border border-white/8 bg-white/[0.04] shadow-panel">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/8 text-left">
                <thead class="bg-slate-950/25 text-xs uppercase tracking-[0.2em] text-white/35">
                    <tr>
                        <th class="px-6 py-4">Circular</th>
                        <th class="px-6 py-4">Window</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Updated</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/8">
                    @forelse ($circulars as $circular)
                        <tr class="align-top">
                            <td class="px-6 py-5">
                                <p class="font-semibold text-white">{{ $circular->tx_title ?: 'Circular' }}</p>
                                <p class="mt-2 max-w-xl text-sm leading-6 text-white/55">{{ $circular->excerpt ?: 'No preview text available.' }}</p>
                            </td>
                            <td class="px-6 py-5 text-sm text-white/70">
                                <p>{{ $circular->start_date_label }}</p>
                                <p class="mt-1 text-white/40">Until {{ $circular->close_date_label }}</p>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full border px-3 py-1 text-xs {{ $circular->is_online ? 'border-emerald-400/20 bg-emerald-500/10 text-emerald-200' : 'border-amber-400/20 bg-amber-500/10 text-amber-100' }}">
                                        {{ $circular->is_online ? 'Online' : 'Offline' }}
                                    </span>
                                    <span class="rounded-full border px-3 py-1 text-xs {{ $circular->is_active ? 'border-sky-400/20 bg-sky-500/10 text-sky-100' : 'border-red-400/20 bg-red-500/10 text-red-100' }}">
                                        {{ $circular->is_active ? 'Active' : 'Archived' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-sm text-white/45">{{ $circular->dtt_mod?->format('M d, Y g:i A') ?? 'Unknown' }}</td>
                            <td class="px-6 py-5">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('admin.circulars.edit', $circular->id_career_key) }}" class="rounded-xl border border-white/10 px-3 py-2 text-sm text-white/75 hover:bg-white/[0.04]">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.circulars.online', $circular->id_career_key) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-xl border border-white/10 px-3 py-2 text-sm text-white/75 hover:bg-white/[0.04]">
                                            {{ $circular->is_online ? 'Set Offline' : 'Publish' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.circulars.active', $circular->id_career_key) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-xl border border-white/10 px-3 py-2 text-sm {{ $circular->is_active ? 'text-red-200 hover:bg-red-500/10' : 'text-emerald-200 hover:bg-emerald-500/10' }}">
                                            {{ $circular->is_active ? 'Archive' : 'Restore' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-sm text-white/45">No circulars found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($circulars->hasPages())
        <div class="flex items-center justify-between gap-3">
            @if ($circulars->onFirstPage())
                <span class="rounded-2xl border border-white/8 px-4 py-3 text-sm text-white/25">Previous</span>
            @else
                <a href="{{ $circulars->previousPageUrl() }}" class="rounded-2xl border border-white/10 px-4 py-3 text-sm text-white/75 hover:bg-white/[0.04]">Previous</a>
            @endif

            <span class="text-sm text-white/45">Page {{ $circulars->currentPage() }} of {{ $circulars->lastPage() }}</span>

            @if ($circulars->hasMorePages())
                <a href="{{ $circulars->nextPageUrl() }}" class="rounded-2xl border border-white/10 px-4 py-3 text-sm text-white/75 hover:bg-white/[0.04]">Next</a>
            @else
                <span class="rounded-2xl border border-white/8 px-4 py-3 text-sm text-white/25">Next</span>
            @endif
        </div>
    @endif
</div>
@endsection
