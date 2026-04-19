@extends('layouts.admin')

@section('page_title', $isEditing ? 'Edit Circular' : 'Create Circular')
@section('page_eyebrow', 'Content')

@section('content')
<form method="POST" action="{{ $isEditing ? route('admin.circulars.update', $circular->id_career_key) : route('admin.circulars.store') }}" class="space-y-6">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="rounded-[1.5rem] border border-red-400/20 bg-red-500/10 px-5 py-4 text-sm text-red-100">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="flex items-center justify-between gap-4">
        <a href="{{ route('admin.circulars.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-white/10 px-4 py-3 text-sm text-white/75 hover:bg-white/[0.04]">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            Back to circulars
        </a>

        <button type="submit" class="rounded-2xl bg-admin-gold px-5 py-3 font-semibold text-admin-ink">
            {{ $isEditing ? 'Update Circular' : 'Save Circular' }}
        </button>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.7fr)_minmax(320px,1fr)]">
        <section class="rounded-[2rem] border border-white/8 bg-white/[0.04] p-6 shadow-panel">
            <h2 class="font-display text-2xl font-bold text-white">Circular content</h2>
            <div class="mt-6 space-y-5">
                <div>
                    <label for="title" class="mb-2 block text-sm font-medium text-white/80">Title</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $circular->tx_title) }}" class="w-full rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-white placeholder:text-white/25 focus:border-admin-gold/40 focus:ring-admin-gold/25" placeholder="Circular title">
                </div>

                <div>
                    <label for="body" class="mb-2 block text-sm font-medium text-white/80">Circular details</label>
                    <textarea id="body" name="body" rows="14" class="w-full rounded-[1.5rem] border border-white/10 bg-white/[0.03] px-4 py-3 text-white placeholder:text-white/25 focus:border-admin-gold/40 focus:ring-admin-gold/25" placeholder="Write the circular details for members...">{{ old('body', $circular->body_text) }}</textarea>
                </div>
            </div>
        </section>

        <section class="space-y-6">
            <div class="rounded-[2rem] border border-white/8 bg-white/[0.04] p-6 shadow-panel">
                <h2 class="font-display text-2xl font-bold text-white">Schedule</h2>
                <div class="mt-6 space-y-4">
                    <div>
                        <label for="publish_at" class="mb-2 block text-sm font-medium text-white/80">Start date & time</label>
                        <input id="publish_at" name="publish_at" type="datetime-local" value="{{ old('publish_at', optional($circular->dtt_ad_start)->format('Y-m-d\\TH:i') ?? now()->format('Y-m-d\\TH:i')) }}" class="w-full rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-white focus:border-admin-gold/40 focus:ring-admin-gold/25">
                    </div>

                    <div>
                        <label for="close_at" class="mb-2 block text-sm font-medium text-white/80">Close date & time</label>
                        <input id="close_at" name="close_at" type="datetime-local" value="{{ old('close_at', optional($circular->dtt_ad_close)->format('Y-m-d\\TH:i')) }}" class="w-full rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-white focus:border-admin-gold/40 focus:ring-admin-gold/25">
                    </div>
                </div>

                <div class="mt-5 space-y-3">
                    <label class="flex items-center justify-between rounded-2xl border border-white/8 bg-slate-950/20 px-4 py-3">
                        <span>
                            <span class="block font-medium text-white">Active record</span>
                            <span class="text-sm text-white/45">Archived circulars stay out of the member panel.</span>
                        </span>
                        <input type="checkbox" name="is_active" value="1" class="rounded border-white/20 bg-white/[0.04] text-admin-gold focus:ring-admin-gold/30" @checked(old('is_active', (bool) $circular->is_active))>
                    </label>

                    <label class="flex items-center justify-between rounded-2xl border border-white/8 bg-slate-950/20 px-4 py-3">
                        <span>
                            <span class="block font-medium text-white">Visible to members</span>
                            <span class="text-sm text-white/45">Only online circulars appear in the member circular archive.</span>
                        </span>
                        <input type="checkbox" name="is_online" value="1" class="rounded border-white/20 bg-white/[0.04] text-admin-gold focus:ring-admin-gold/30" @checked(old('is_online', (bool) $circular->is_online))>
                    </label>
                </div>
            </div>

            <div class="rounded-[2rem] border border-white/8 bg-white/[0.04] p-6 shadow-panel">
                <h2 class="font-display text-2xl font-bold text-white">Metadata</h2>
                <div class="mt-6 space-y-4">
                    <div>
                        <label for="external_url" class="mb-2 block text-sm font-medium text-white/80">External URL</label>
                        <input id="external_url" name="external_url" type="text" value="{{ old('external_url', $circular->action_url) }}" class="w-full rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-white placeholder:text-white/25 focus:border-admin-gold/40 focus:ring-admin-gold/25" placeholder="Optional external URL">
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                        <div>
                            <label for="hash" class="mb-2 block text-sm font-medium text-white/80">Hash</label>
                            <input id="hash" name="hash" type="text" value="{{ old('hash', \App\Support\PortalContent::cleanedOptionalField($circular->tx_hash)) }}" class="w-full rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-white focus:border-admin-gold/40 focus:ring-admin-gold/25">
                        </div>

                        <div>
                            <label for="tag" class="mb-2 block text-sm font-medium text-white/80">Tag</label>
                            <input id="tag" name="tag" type="text" value="{{ old('tag', \App\Support\PortalContent::cleanedOptionalField($circular->tx_tag)) }}" class="w-full rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-white focus:border-admin-gold/40 focus:ring-admin-gold/25">
                        </div>
                    </div>

                    <div>
                        <label for="career_type" class="mb-2 block text-sm font-medium text-white/80">Type</label>
                        <input id="career_type" name="career_type" type="text" value="{{ old('career_type', \App\Support\PortalContent::cleanedOptionalField($circular->tx_career_type)) }}" class="w-full rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-white focus:border-admin-gold/40 focus:ring-admin-gold/25">
                    </div>

                    <div>
                        <label for="address" class="mb-2 block text-sm font-medium text-white/80">Address</label>
                        <input id="address" name="address" type="text" value="{{ old('address', \App\Support\PortalContent::cleanedOptionalField($circular->tx_address)) }}" class="w-full rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-white focus:border-admin-gold/40 focus:ring-admin-gold/25">
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                        <div>
                            <label for="phone" class="mb-2 block text-sm font-medium text-white/80">Phone</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone', \App\Support\PortalContent::cleanedOptionalField($circular->tx_phone)) }}" class="w-full rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-white focus:border-admin-gold/40 focus:ring-admin-gold/25">
                        </div>

                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-white/80">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email', \App\Support\PortalContent::cleanedOptionalField($circular->tx_email)) }}" class="w-full rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-white focus:border-admin-gold/40 focus:ring-admin-gold/25">
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</form>
@endsection
