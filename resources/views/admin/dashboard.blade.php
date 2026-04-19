@extends('layouts.admin')

@section('page_title', 'Dashboard')
@section('page_eyebrow', 'Overview')

@section('content')
<div class="space-y-6">
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[1.75rem] border border-white/8 bg-white/[0.04] p-5 shadow-panel">
            <p class="text-xs uppercase tracking-[0.2em] text-white/35">Admin Users</p>
            <p class="mt-4 font-display text-4xl font-bold text-white">{{ number_format($stats['admins']) }}</p>
            <p class="mt-2 text-sm text-white/55">Separate guard backed by the `Users` table.</p>
        </div>

        <div class="rounded-[1.75rem] border border-white/8 bg-white/[0.04] p-5 shadow-panel">
            <p class="text-xs uppercase tracking-[0.2em] text-white/35">Published Notices</p>
            <p class="mt-4 font-display text-4xl font-bold text-white">{{ number_format($stats['notices_published']) }}</p>
            <p class="mt-2 text-sm text-white/55">{{ number_format($stats['notices_total']) }} total notice records.</p>
        </div>

        <div class="rounded-[1.75rem] border border-white/8 bg-white/[0.04] p-5 shadow-panel">
            <p class="text-xs uppercase tracking-[0.2em] text-white/35">Published Circulars</p>
            <p class="mt-4 font-display text-4xl font-bold text-white">{{ number_format($stats['circulars_published']) }}</p>
            <p class="mt-2 text-sm text-white/55">{{ number_format($stats['circulars_total']) }} total circular records.</p>
        </div>

        <div class="rounded-[1.75rem] border border-admin-gold/20 bg-admin-gold/10 p-5 shadow-panel">
            <p class="text-xs uppercase tracking-[0.2em] text-admin-gold">Direct Publish</p>
            <p class="mt-4 font-display text-3xl font-bold text-white">Member portal stays in sync</p>
            <p class="mt-2 text-sm text-white/70">Notices use `T_MESSAGE`, circulars use `T_CAREER`.</p>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <article class="rounded-[2rem] border border-white/8 bg-white/[0.04] p-6 shadow-panel">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-white/35">Recent Notices</p>
                    <h2 class="mt-2 font-display text-2xl font-bold text-white">Latest notice activity</h2>
                </div>
                <a href="{{ route('admin.notices.create') }}" class="rounded-2xl bg-admin-gold px-4 py-2 text-sm font-semibold text-admin-ink">New Notice</a>
            </div>

            <div class="mt-6 space-y-3">
                @forelse ($recentNotices as $notice)
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/20 px-4 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-white">{{ $notice->tx_title ?: 'Notice' }}</p>
                                <p class="mt-1 text-sm text-white/55">{{ $notice->excerpt ?: 'No preview text available.' }}</p>
                            </div>
                            <span class="rounded-full border px-3 py-1 text-xs {{ $notice->is_online ? 'border-emerald-400/20 bg-emerald-500/10 text-emerald-200' : 'border-amber-400/20 bg-amber-500/10 text-amber-100' }}">
                                {{ $notice->is_online ? 'Online' : 'Offline' }}
                            </span>
                        </div>
                        <div class="mt-3 flex items-center justify-between text-xs text-white/40">
                            <span>{{ $notice->published_date_label }}</span>
                            <a href="{{ route('admin.notices.edit', $notice->id_message_key) }}" class="text-admin-gold">Edit</a>
                        </div>
                    </div>
                @empty
                    <div class="rounded-[1.5rem] border border-dashed border-white/10 px-4 py-10 text-center text-sm text-white/45">
                        No notices found.
                    </div>
                @endforelse
            </div>
        </article>

        <article class="rounded-[2rem] border border-white/8 bg-white/[0.04] p-6 shadow-panel">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-white/35">Recent Circulars</p>
                    <h2 class="mt-2 font-display text-2xl font-bold text-white">Latest circular activity</h2>
                </div>
                <a href="{{ route('admin.circulars.create') }}" class="rounded-2xl bg-admin-gold px-4 py-2 text-sm font-semibold text-admin-ink">New Circular</a>
            </div>

            <div class="mt-6 space-y-3">
                @forelse ($recentCirculars as $circular)
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/20 px-4 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-white">{{ $circular->tx_title ?: 'Circular' }}</p>
                                <p class="mt-1 text-sm text-white/55">{{ $circular->excerpt ?: 'No preview text available.' }}</p>
                            </div>
                            <span class="rounded-full border px-3 py-1 text-xs {{ $circular->is_online ? 'border-emerald-400/20 bg-emerald-500/10 text-emerald-200' : 'border-amber-400/20 bg-amber-500/10 text-amber-100' }}">
                                {{ $circular->is_online ? 'Online' : 'Offline' }}
                            </span>
                        </div>
                        <div class="mt-3 flex items-center justify-between text-xs text-white/40">
                            <span>{{ $circular->start_date_label }}</span>
                            <a href="{{ route('admin.circulars.edit', $circular->id_career_key) }}" class="text-admin-gold">Edit</a>
                        </div>
                    </div>
                @empty
                    <div class="rounded-[1.5rem] border border-dashed border-white/10 px-4 py-10 text-center text-sm text-white/45">
                        No circulars found.
                    </div>
                @endforelse
            </div>
        </article>
    </section>
</div>
@endsection
