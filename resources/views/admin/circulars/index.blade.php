@extends('layouts.admin')

@section('page_title', 'Circulars')
@section('page_eyebrow', 'Content')

@section('content')
<div class="space-y-4">
    <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[10px] uppercase tracking-[0.24em] text-white/35">Circular Manager</p>
                <h2 class="mt-1 font-display text-xl font-bold text-white">Manage Circulars</h2>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <form
                    method="GET"
                    action="{{ route('admin.circulars.index') }}"
                    x-data="{
                        timer: null,
                        queueSearch() {
                            clearTimeout(this.timer);
                            this.timer = setTimeout(() => this.$el.requestSubmit(), 350);
                        }
                    }"
                    class="flex min-w-[18rem] items-center gap-2 border border-[#30384a] bg-slate-950/20 px-3 py-2"
                >
                    <span class="material-symbols-outlined text-[18px] text-white/35">search</span>
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        @input="queueSearch()"
                        class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-white placeholder:text-white/25 focus:ring-0"
                    >
                    @if ($search !== '')
                        <a href="{{ route('admin.circulars.index') }}" class="text-[11px] uppercase tracking-[0.16em] text-white/45 transition hover:text-white/75">Clear</a>
                    @endif
                </form>

                <a href="{{ route('admin.circulars.create') }}" class="inline-flex h-10 items-center justify-center border border-[#30384a] bg-admin-gold px-4 text-sm font-medium text-admin-ink">
                    Create Circular
                </a>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-admin-line/10 bg-white/[0.03] shadow-panel">
        <div class="flex items-center justify-between border-b border-admin-line/10 px-4 py-3">
            <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">Circular List</p>
            <p class="text-xs text-white/45">{{ $circulars->total() }} records</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-admin-line/10 bg-slate-950/20 text-[10px] uppercase tracking-[0.2em] text-white/35">
                    <tr>
                        <th class="px-4 py-3 font-medium">Circular</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Updated</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-admin-line/10">
                    @forelse ($circulars as $circular)
                        <tr class="align-top">
                            <td class="px-4 py-3.5">
                                <p class="text-sm font-semibold text-white">{{ $circular->tx_title ?: 'Circular' }}</p>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="inline-flex items-center border border-[#30384a] px-2 py-1 text-[11px] uppercase tracking-[0.16em] {{ $circular->is_online ? 'text-admin-gold' : 'text-white/45' }}">
                                        {{ $circular->is_online ? 'Online' : 'Offline' }}
                                    </span>
                                    <span class="inline-flex items-center border border-[#30384a] px-2 py-1 text-[11px] uppercase tracking-[0.16em] text-white/55">
                                        {{ $circular->is_active ? 'Active' : 'Archived' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-white/45">{{ $circular->dtt_mod?->format('M d, Y g:i A') ?? 'Unknown' }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('admin.circulars.edit', $circular->id_career_key) }}" class="inline-flex h-8 items-center border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.circulars.online', $circular->id_career_key) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex h-8 items-center border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                                            {{ $circular->is_online ? 'Offline' : 'Publish' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.circulars.active', $circular->id_career_key) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex h-8 items-center border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                                            {{ $circular->is_active ? 'Archive' : 'Restore' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-sm text-white/45">
                                {{ $search !== '' ? 'No circulars matched your search.' : 'No circulars found.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3">
            @include('admin.partials.pagination', ['paginator' => $circulars])
        </div>
    </section>
</div>
@endsection
