@extends('layouts.admin')

@section('page_title', 'Notices')
@section('page_eyebrow', 'Content')

@section('content')
<div class="space-y-4">
    <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[10px] uppercase tracking-[0.24em] text-white/35">Notice Manager</p>
                <h2 class="mt-1 font-display text-xl font-bold text-white">Manage Notices</h2>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <form
                    method="GET"
                    action="{{ route('admin.notices.index') }}"
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
                        placeholder="Search notices"
                        @input="queueSearch()"
                        class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-white placeholder:text-white/25 focus:ring-0"
                    >
                    @if ($search !== '')
                        <a href="{{ route('admin.notices.index') }}" class="text-[11px] uppercase tracking-[0.16em] text-white/45 transition hover:text-white/75">Clear</a>
                    @endif
                </form>

                <a href="{{ route('admin.notices.create') }}" class="inline-flex h-10 items-center justify-center border border-[#30384a] bg-admin-gold px-4 text-sm font-medium text-admin-ink">
                    Create Notice
                </a>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-admin-line/10 bg-white/[0.03] shadow-panel">
        <div class="flex items-center justify-between border-b border-admin-line/10 px-4 py-3">
            <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">Notice List</p>
            <p class="text-xs text-white/45">{{ $notices->total() }} records</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-admin-line/10 bg-slate-950/20 text-[10px] uppercase tracking-[0.2em] text-white/35">
                    <tr>
                        <th class="px-4 py-3 font-medium">Notice</th>
                        <th class="px-4 py-3 font-medium">Publish Date</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Updated</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-admin-line/10">
                    @forelse ($notices as $notice)
                        <tr class="align-top">
                            <td class="px-4 py-3.5">
                                <p class="text-sm font-semibold text-white">{{ $notice->tx_title ?: 'Notice' }}</p>
                                @if ($notice->excerpt)
                                    <p class="mt-1 max-w-2xl text-xs leading-5 text-white/48">{{ $notice->excerpt }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-xs text-white/65">{{ $notice->published_date_label }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="inline-flex items-center border border-[#30384a] px-2 py-1 text-[11px] uppercase tracking-[0.16em] {{ $notice->is_online ? 'text-admin-gold' : 'text-white/45' }}">
                                        {{ $notice->is_online ? 'Visible' : 'Hidden' }}
                                    </span>
                                    <span class="inline-flex items-center border border-[#30384a] px-2 py-1 text-[11px] uppercase tracking-[0.16em] text-white/55">
                                        {{ $notice->is_active ? 'Active' : 'Archived' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-white/45">{{ $notice->dtt_mod?->format('M d, Y g:i A') ?? 'Unknown' }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('admin.notices.edit', $notice->id_message_key) }}" class="inline-flex h-8 items-center border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.notices.online', $notice->id_message_key) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex h-8 items-center border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                                            {{ $notice->is_online ? 'Hide' : 'Show' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.notices.active', $notice->id_message_key) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex h-8 items-center border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                                            {{ $notice->is_active ? 'Archive' : 'Restore' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-sm text-white/45">
                                {{ $search !== '' ? 'No notices matched your search.' : 'No notices found.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3">
            @include('admin.partials.pagination', ['paginator' => $notices])
        </div>
    </section>
</div>
@endsection
