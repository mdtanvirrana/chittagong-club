@extends('layouts.admin')

@section('page_title', 'Upload Gallary')
@section('page_eyebrow', 'Media')

@section('content')
<div class="space-y-4">
    <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
        <div class="flex flex-col gap-4 border-b border-admin-line/10 pb-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">Album Upload</p>
                <p class="mt-1 text-sm text-white/45">Create an album name, then upload one or many gallery images into that album.</p>
            </div>

            <a
                href="{{ route('gallery') }}"
                target="_blank"
                rel="noreferrer"
                class="inline-flex h-10 items-center justify-center rounded-xl border border-[#30384a] px-4 text-sm font-medium text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]"
            >
                View Member Gallery
            </a>
        </div>

        <form
            method="POST"
            action="{{ route('admin.gallery.store') }}"
            enctype="multipart/form-data"
            class="mt-4 space-y-4"
            x-data="{
                isDragging: false,
                albumName: @js(old('album_name', '')),
                previews: [],
                formatSize(bytes) {
                    if (! bytes) {
                        return '0 KB';
                    }

                    if (bytes >= 1048576) {
                        return `${(bytes / 1048576).toFixed(2)} MB`;
                    }

                    return `${(bytes / 1024).toFixed(1)} KB`;
                },
                revokePreviews() {
                    this.previews.forEach((preview) => {
                        if (preview.url) {
                            URL.revokeObjectURL(preview.url);
                        }
                    });
                },
                syncFiles(files) {
                    const pickedFiles = Array.from(files || []);
                    this.revokePreviews();
                    this.previews = pickedFiles.map((file) => ({
                        name: file.name,
                        size: this.formatSize(file.size),
                        url: URL.createObjectURL(file),
                    }));

                    if (! this.$refs.images) {
                        return;
                    }

                    if (typeof DataTransfer !== 'undefined') {
                        const transfer = new DataTransfer();
                        pickedFiles.forEach((file) => transfer.items.add(file));
                        this.$refs.images.files = transfer.files;
                        return;
                    }

                    this.$refs.images.files = files;
                },
                onBrowse(event) {
                    this.syncFiles(event.target.files);
                },
                onDrop(event) {
                    this.isDragging = false;
                    this.syncFiles(event.dataTransfer.files);
                },
                clearFiles() {
                    this.revokePreviews();
                    this.previews = [];

                    if (this.$refs.images) {
                        this.$refs.images.value = '';
                    }
                }
            }"
        >
            @csrf

            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                <div>
                    <label for="album_name" class="block text-xs font-semibold uppercase tracking-[0.16em] text-white/55">Album Name</label>
                    <input
                        x-ref="albumName"
                        x-model="albumName"
                        id="album_name"
                        name="album_name"
                        type="text"
                        value="{{ old('album_name') }}"
                        maxlength="120"
                        placeholder="Example: Annual Picnic 2026"
                        class="mt-2 h-11 w-full rounded-xl border border-[#30384a] bg-white/[0.04] px-3 text-sm text-white placeholder:text-white/30 focus:border-admin-gold focus:outline-none focus:ring-2 focus:ring-admin-gold/20"
                    >
                    @error('album_name')
                        <p class="mt-2 text-xs text-red-300">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    :disabled="albumName.trim() === '' || previews.length === 0"
                    class="inline-flex h-11 items-center justify-center rounded-xl border border-admin-gold/20 bg-admin-gold px-5 text-sm font-semibold text-admin-ink transition disabled:cursor-not-allowed disabled:opacity-60"
                >
                    Upload Album
                </button>
            </div>

            <div>
                <input
                    x-ref="images"
                    id="images"
                    name="images[]"
                    type="file"
                    multiple
                    accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif"
                    class="sr-only"
                    @change="onBrowse($event)"
                >

                <div
                    @click="$refs.images.click()"
                    @dragenter.prevent="isDragging = true"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="if (! $el.contains($event.relatedTarget)) { isDragging = false }"
                    @drop.prevent="onDrop($event)"
                    :class="isDragging ? 'border-admin-gold bg-admin-gold/10' : 'border-[#30384a] bg-slate-950/20'"
                    class="cursor-pointer rounded-xl border border-dashed px-5 py-8 text-center transition"
                >
                    <div class="mx-auto flex max-w-xl flex-col items-center gap-3">
                        <span class="material-symbols-outlined text-4xl text-admin-gold">add_photo_alternate</span>
                        <div class="space-y-1">
                            <p class="text-base font-semibold text-white">Drag and drop gallery images here</p>
                            <p class="text-sm text-white/55">or click to browse one or many files</p>
                        </div>
                        <p class="text-xs uppercase tracking-[0.18em] text-white/40">JPG, PNG, WEBP, GIF up to 10MB each</p>
                    </div>
                </div>

                <div x-cloak x-show="previews.length > 0" class="mt-3 rounded-lg border border-admin-line/10 bg-slate-950/20 p-3">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs font-medium uppercase tracking-[0.16em] text-white/55">
                            <span x-text="previews.length"></span> file(s) selected
                        </p>
                        <button type="button" @click.stop="clearFiles()" class="inline-flex h-8 items-center rounded-xl border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                            Clear
                        </button>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-8">
                        <template x-for="preview in previews" :key="preview.url">
                            <article class="overflow-hidden rounded-lg border border-admin-line/10 bg-white/[0.04]">
                                <div class="aspect-square overflow-hidden bg-slate-950/40">
                                    <img :src="preview.url" :alt="preview.name" class="h-full w-full object-contain">
                                </div>
                                <div class="space-y-1 px-2 py-2">
                                    <p class="truncate text-xs font-semibold text-white" x-text="preview.name"></p>
                                    <p class="text-xs text-white/45" x-text="preview.size"></p>
                                </div>
                            </article>
                        </template>
                    </div>
                </div>

                @error('images')
                    <p class="mt-2 text-xs text-red-300">{{ $message }}</p>
                @enderror
                @error('images.*')
                    <p class="mt-2 text-xs text-red-300">{{ $message }}</p>
                @enderror
            </div>
        </form>
    </section>

    <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
        <div class="flex flex-col gap-1 border-b border-admin-line/10 pb-4">
            <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">Existing Albums</p>
            <p class="text-sm text-white/45">Use the same album name again to add more images to that album.</p>
        </div>

        @if ($albums->isEmpty())
            <div class="px-4 py-12 text-center text-sm text-white/45">
                No gallery albums uploaded yet.
            </div>
        @else
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                @foreach ($albums as $album)
                    <article class="overflow-hidden rounded-lg border border-admin-line/10 bg-slate-950/20">
                        <div class="aspect-[16/9] overflow-hidden bg-slate-950/40">
                            <img src="{{ $album['cover'] }}" alt="{{ $album['title'] }}" class="h-full w-full object-cover">
                        </div>
                        <div class="space-y-3 p-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-white">{{ $album['title'] }}</p>
                                <p class="mt-1 text-xs text-white/45">{{ $album['photo_count'] }} photo(s) - {{ $album['date'] }}</p>
                            </div>

                            <button
                                type="button"
                                onclick="const input = document.getElementById('album_name'); input.value = @js($album['title']); input.dispatchEvent(new Event('input', { bubbles: true })); input.focus();"
                                class="inline-flex h-9 w-full items-center justify-center rounded-xl border border-[#30384a] px-3 text-xs font-medium text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]"
                            >
                                Add More Images
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
