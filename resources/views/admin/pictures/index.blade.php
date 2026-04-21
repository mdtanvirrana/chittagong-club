@extends('layouts.admin')

@section('page_title', 'Upload Pictures')
@section('page_eyebrow', 'Media')

@section('content')
<div class="space-y-4">
    <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
        <form
            method="POST"
            action="{{ route('admin.pictures.store') }}"
            enctype="multipart/form-data"
            class="space-y-4"
            x-data="{
                isDragging: false,
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

            <div class="flex items-center justify-between gap-3">
                <p class="text-xs text-white/45">You can select one image or multiple images in the same upload.</p>
                <button type="submit" class="inline-flex h-9 items-center justify-center border border-[#30384a] bg-admin-gold px-4 text-sm font-medium text-admin-ink">
                    Upload Pictures
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
                    class="rounded-xl border border-dashed px-5 py-8 text-center transition cursor-pointer"
                >
                    <div class="mx-auto flex max-w-xl flex-col items-center gap-3">
                        <span class="material-symbols-outlined text-4xl text-admin-gold">cloud_upload</span>
                        <div class="space-y-1">
                            <p class="text-base font-semibold text-white">Drag and drop images here</p>
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
                        <button type="button" @click.stop="clearFiles()" class="inline-flex h-8 items-center border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
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
        <div class="flex items-center justify-between gap-4 border-b border-admin-line/10 pb-3">
            <div>
                <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">Recent Files</p>
            </div>
            <p class="text-xs text-white/45">
                @if ($pictures->total() > 0)
                    {{ $pictures->firstItem() }}-{{ $pictures->lastItem() }} of {{ $pictures->total() }}
                @else
                    0 shown
                @endif
            </p>
        </div>

        @if ($pictures->isEmpty())
            <div class="px-4 py-12 text-center text-sm text-white/45">
                No image files found in `public/images`.
            </div>
        @else
            <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-8">
                @foreach ($pictures as $picture)
                    <article class="overflow-hidden rounded-lg border border-admin-line/10 bg-slate-950/20">
                        <div class="aspect-square overflow-hidden bg-slate-950/40">
                            <img src="{{ $picture['url'] }}" alt="{{ $picture['name'] }}" class="h-full w-full object-cover">
                        </div>
                        <div class="space-y-1.5 px-2 py-2">
                            <p class="truncate text-xs font-semibold text-white">{{ $picture['name'] }}</p>
                            <p class="text-xs text-white/45">{{ $picture['size_kb'] }} KB</p>
                            <p class="truncate text-[11px] text-white/35">{{ $picture['updated_at'] }}</p>
                            <div class="flex items-center gap-1.5">
                                <a href="{{ $picture['url'] }}" target="_blank" rel="noreferrer" class="inline-flex h-7 flex-1 items-center justify-center border border-[#30384a] px-2 text-[11px] text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                                    Open
                                </a>
                                <form method="POST" action="{{ route('admin.pictures.destroy') }}" onsubmit="return confirm('Delete this image?')" class="flex-1">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="filename" value="{{ $picture['name'] }}">
                                    <input type="hidden" name="page" value="{{ $pictures->currentPage() }}">
                                    <button type="submit" class="inline-flex h-7 w-full items-center justify-center border border-red-400/25 px-2 text-[11px] text-red-200 transition hover:border-red-300/40 hover:bg-red-500/10">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($pictures->hasPages())
                <div class="mt-4 flex flex-col gap-3 border-t border-admin-line/10 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-white/45">
                        Page {{ $pictures->currentPage() }} of {{ $pictures->lastPage() }}
                    </p>

                    <div class="flex items-center gap-2">
                        @if ($pictures->onFirstPage())
                            <span class="inline-flex h-8 items-center justify-center border border-[#30384a] px-3 text-xs text-white/30">
                                Previous
                            </span>
                        @else
                            <a href="{{ $pictures->previousPageUrl() }}" class="inline-flex h-8 items-center justify-center border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                                Previous
                            </a>
                        @endif

                        <span class="inline-flex h-8 items-center justify-center border border-admin-gold/20 bg-admin-gold/10 px-3 text-xs font-semibold text-admin-gold">
                            {{ $pictures->currentPage() }}
                        </span>

                        @if ($pictures->hasMorePages())
                            <a href="{{ $pictures->nextPageUrl() }}" class="inline-flex h-8 items-center justify-center border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                                Next
                            </a>
                        @else
                            <span class="inline-flex h-8 items-center justify-center border border-[#30384a] px-3 text-xs text-white/30">
                                Next
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </section>
</div>
@endsection
