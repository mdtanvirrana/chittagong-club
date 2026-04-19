@extends('layouts.admin')

@section('page_title', $isEditing ? 'Edit Notice' : 'Create Notice')
@section('page_eyebrow', 'Content')

@section('content')
<form method="POST" action="{{ $isEditing ? route('admin.notices.update', $notice->id_message_key) : route('admin.notices.store') }}" class="space-y-6">
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
        <a href="{{ route('admin.notices.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-white/10 px-4 py-3 text-sm text-white/75 hover:bg-white/[0.04]">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            Back to notices
        </a>

        <button type="submit" class="rounded-2xl bg-admin-gold px-5 py-3 font-semibold text-admin-ink">
            {{ $isEditing ? 'Update Notice' : 'Save Notice' }}
        </button>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.7fr)_minmax(320px,1fr)]">
        <section class="rounded-[2rem] border border-white/8 bg-white/[0.04] p-6 shadow-panel">
            <h2 class="font-display text-2xl font-bold text-white">Notice content</h2>
            <div class="mt-6 space-y-5">
                <div>
                    <label for="title" class="mb-2 block text-sm font-medium text-white/80">Title</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $notice->tx_title) }}" class="w-full rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-white placeholder:text-white/25 focus:border-admin-gold/40 focus:ring-admin-gold/25" placeholder="Notice title">
                </div>

                <div>
                    <label for="body" class="mb-2 block text-sm font-medium text-white/80">Message body</label>
                    <textarea id="body" name="body" rows="14" class="w-full rounded-[1.5rem] border border-white/10 bg-white/[0.03] px-4 py-3 text-white placeholder:text-white/25 focus:border-admin-gold/40 focus:ring-admin-gold/25" placeholder="Write the full notice for members...">{{ old('body', $notice->body_text) }}</textarea>
                </div>
            </div>
        </section>

        <section class="space-y-6">
            <div class="rounded-[2rem] border border-white/8 bg-white/[0.04] p-6 shadow-panel">
                <h2 class="font-display text-2xl font-bold text-white">Publishing</h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-1">
                    <div>
                        <label for="publish_date" class="mb-2 block text-sm font-medium text-white/80">Publish date</label>
                        <input id="publish_date" name="publish_date" type="date" value="{{ old('publish_date', optional($notice->Edate)->format('Y-m-d') ?? now()->toDateString()) }}" class="w-full rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-white focus:border-admin-gold/40 focus:ring-admin-gold/25">
                    </div>

                    <div>
                        <label for="publish_time" class="mb-2 block text-sm font-medium text-white/80">Publish time</label>
                        <input id="publish_time" name="publish_time" type="time" value="{{ old('publish_time', $notice->publish_time_for_form) }}" class="w-full rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-white focus:border-admin-gold/40 focus:ring-admin-gold/25">
                    </div>
                </div>

                <div class="mt-5 space-y-3">
                    <label class="flex items-center justify-between rounded-2xl border border-white/8 bg-slate-950/20 px-4 py-3">
                        <span>
                            <span class="block font-medium text-white">Active record</span>
                            <span class="text-sm text-white/45">Archived notices stay out of the member panel.</span>
                        </span>
                        <input type="checkbox" name="is_active" value="1" class="rounded border-white/20 bg-white/[0.04] text-admin-gold focus:ring-admin-gold/30" @checked(old('is_active', (bool) $notice->is_active))>
                    </label>

                    <label class="flex items-center justify-between rounded-2xl border border-white/8 bg-slate-950/20 px-4 py-3">
                        <span>
                            <span class="block font-medium text-white">Visible to members</span>
                            <span class="text-sm text-white/45">Only online notices appear on the member notice board.</span>
                        </span>
                        <input type="checkbox" name="is_online" value="1" class="rounded border-white/20 bg-white/[0.04] text-admin-gold focus:ring-admin-gold/30" @checked(old('is_online', (bool) $notice->is_online))>
                    </label>
                </div>
            </div>

            <div class="rounded-[2rem] border border-white/8 bg-white/[0.04] p-6 shadow-panel">
                <h2 class="font-display text-2xl font-bold text-white">Optional metadata</h2>
                <div class="mt-6 space-y-4">
                    <div>
                        <label for="image_url" class="mb-2 block text-sm font-medium text-white/80">Image URL</label>
                        <input id="image_url" name="image_url" type="text" value="{{ old('image_url', $notice->image_url) }}" class="w-full rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-white placeholder:text-white/25 focus:border-admin-gold/40 focus:ring-admin-gold/25" placeholder="Optional external image URL">
                    </div>

                    <div>
                        <label for="post_url" class="mb-2 block text-sm font-medium text-white/80">Link URL</label>
                        <input id="post_url" name="post_url" type="text" value="{{ old('post_url', $notice->post_url) }}" class="w-full rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-white placeholder:text-white/25 focus:border-admin-gold/40 focus:ring-admin-gold/25" placeholder="Optional external link">
                    </div>

                    <div>
                        <label for="comment" class="mb-2 block text-sm font-medium text-white/80">Comment</label>
                        <input id="comment" name="comment" type="text" value="{{ old('comment', \App\Support\PortalContent::cleanedOptionalField($notice->tx_comment)) }}" class="w-full rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-white placeholder:text-white/25 focus:border-admin-gold/40 focus:ring-admin-gold/25" placeholder="Internal note">
                    </div>
                </div>
            </div>
        </section>
    </div>
</form>
@endsection
