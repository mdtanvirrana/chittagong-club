@extends('layouts.admin')

@section('page_title', 'Dashboard')
@section('page_eyebrow', 'Overview')

@section('content')
<div class="space-y-4">
    <section class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
            <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">Admin Users</p>
            <p class="mt-2 font-display text-3xl font-bold text-white">{{ number_format($stats['admins']) }}</p>
        </div>

        <div class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
            <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">Published Notices</p>
            <p class="mt-2 font-display text-3xl font-bold text-white">{{ number_format($stats['notices_published']) }}</p>
            <p class="mt-1 text-xs text-white/45">{{ number_format($stats['notices_total']) }} total</p>
        </div>

        <div class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
            <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">Published Circulars</p>
            <p class="mt-2 font-display text-3xl font-bold text-white">{{ number_format($stats['circulars_published']) }}</p>
            <p class="mt-1 text-xs text-white/45">{{ number_format($stats['circulars_total']) }} total</p>
        </div>

        <div class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
            <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">Quick Actions</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <a href="{{ route('admin.notices.create') }}" class="inline-flex h-8 items-center border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                    New Notice
                </a>
                <a href="{{ route('admin.circulars.create') }}" class="inline-flex h-8 items-center border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                    New Circular
                </a>
            </div>
        </div>
    </section>

</div>
@endsection
