@extends('layouts.admin')

@section('page_title', $isEditing ? 'Edit Circular' : 'Create Circular')
@section('page_eyebrow', 'Content')

@section('content')
<form method="POST" action="{{ $isEditing ? route('admin.circulars.update', $circular->id_career_key) : route('admin.circulars.store') }}" class="space-y-4">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-admin-line/12 bg-white/[0.04] px-4 py-3 text-xs text-white/75">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('admin.circulars.index') }}" class="inline-flex h-9 items-center gap-2 border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Back to circulars
        </a>

        <button type="submit" class="inline-flex h-9 items-center justify-center border border-[#30384a] bg-admin-gold px-4 text-sm font-medium text-admin-ink">
            {{ $isEditing ? 'Update Circular' : 'Save Circular' }}
        </button>
    </div>

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.8fr)_minmax(280px,0.95fr)]">
        <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
            <h2 class="font-display text-lg font-bold text-white">Circular Content</h2>
            <div class="mt-4 space-y-4">
                <div>
                    <label for="title" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Title</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $circular->tx_title) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0" placeholder="Circular title">
                </div>

                <div>
                    <label for="body" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Body</label>
                    <textarea id="body" name="body" rows="14" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0" placeholder="Write the circular">{{ old('body', $circular->body_text) }}</textarea>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
                <h2 class="font-display text-lg font-bold text-white">Schedule</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <label for="publish_at" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Start</label>
                        <input id="publish_at" name="publish_at" type="datetime-local" value="{{ old('publish_at', optional($circular->dtt_ad_start)->format('Y-m-d\\TH:i') ?? now()->format('Y-m-d\\TH:i')) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white focus:border-[#3b4557] focus:ring-0">
                    </div>

                    <div>
                        <label for="close_at" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Close</label>
                        <input id="close_at" name="close_at" type="datetime-local" value="{{ old('close_at', optional($circular->dtt_ad_close)->format('Y-m-d\\TH:i')) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white focus:border-[#3b4557] focus:ring-0">
                    </div>
                </div>

                <div class="mt-4 space-y-2">
                    <label class="flex items-center justify-between border border-admin-line/10 bg-slate-950/20 px-3 py-2.5">
                        <span class="text-sm text-white/78">Active</span>
                        <input type="checkbox" name="is_active" value="1" class="rounded-none border-[#30384a] bg-transparent text-admin-gold focus:ring-0" @checked(old('is_active', (bool) $circular->is_active))>
                    </label>

                    <label class="flex items-center justify-between border border-admin-line/10 bg-slate-950/20 px-3 py-2.5">
                        <span class="text-sm text-white/78">Visible</span>
                        <input type="checkbox" name="is_online" value="1" class="rounded-none border-[#30384a] bg-transparent text-admin-gold focus:ring-0" @checked(old('is_online', (bool) $circular->is_online))>
                    </label>
                </div>
            </div>

        </section>
    </div>
</form>
@endsection
