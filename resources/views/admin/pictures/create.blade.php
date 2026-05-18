@extends('layouts.admin')

@section('page_title', 'Upload Pictures')
@section('page_eyebrow', 'Media')

@section('content')
<div class="space-y-4">
    <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
        <div class="flex flex-col gap-4 border-b border-admin-line/10 pb-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">Upload</p>
                <p class="mt-1 text-sm text-white/45">Select one photo type, then upload one image or multiple images into that folder.</p>
            </div>

            <a
                href="{{ route('admin.pictures.index') }}"
                class="inline-flex h-10 items-center justify-center rounded-xl border border-[#30384a] px-4 text-sm font-medium text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]"
            >
                Browse Folders
            </a>
        </div>

        <form
            method="POST"
            action="{{ route('admin.pictures.store') }}"
            enctype="multipart/form-data"
            class="mt-4 space-y-4"
            x-data="{
                isDragging: false,
                selectedTarget: @js(old('image_type', '')),
                selectedDepartment: @js(old('department_id', '')),
                uploadTargets: @js($uploadTargets),
                departments: @js($departments),
                previews: [],
                facilitiesTarget: 'facilities_photo',
                needsDepartment() {
                    return this.selectedTarget === this.facilitiesTarget;
                },
                targetLabel(value) {
                    return this.uploadTargets[value]?.label || '';
                },
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
                <input type="hidden" name="image_type" :value="selectedTarget">
                <input type="hidden" name="department_id" :value="needsDepartment() ? selectedDepartment : ''">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-[0.16em] text-white/55">Photo Type</label>
                    <p x-cloak x-show="selectedTarget" class="mt-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-admin-gold" x-text="targetLabel(selectedTarget)"></p>
                </div>

                <button
                    type="submit"
                    :disabled="!selectedTarget || previews.length === 0 || (needsDepartment() && !selectedDepartment)"
                    class="inline-flex h-9 items-center justify-center rounded-xl border border-admin-gold/20 bg-admin-gold px-4 text-sm font-medium text-admin-ink transition disabled:cursor-not-allowed disabled:opacity-60"
                >
                    Upload Pictures
                </button>
            </div>

            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($uploadTargets as $targetKey => $target)
                    <button
                        type="button"
                        @click="selectedTarget = '{{ $targetKey }}'"
                        :class="selectedTarget === '{{ $targetKey }}'
                            ? 'border-admin-gold bg-admin-gold/10 text-admin-gold shadow-sm'
                            : 'border-[#30384a] bg-white/[0.04] text-white/72 hover:border-[#3b4557] hover:bg-white/[0.06]'"
                        class="flex min-h-[3.25rem] items-center justify-between gap-3 rounded-xl border px-3 py-3 text-left transition"
                    >
                        <span class="text-[13px] font-semibold leading-snug">{{ $target['label'] }}</span>
                        <span
                            x-cloak
                            x-show="selectedTarget === '{{ $targetKey }}'"
                            class="material-symbols-outlined shrink-0 text-base"
                        >check_circle</span>
                    </button>
                @endforeach
            </div>

            @error('image_type')
                <p class="text-xs text-red-300">{{ $message }}</p>
            @enderror
            @error('department_id')
                <p class="text-xs text-red-300">{{ $message }}</p>
            @enderror

            <div
                x-cloak
                x-show="needsDepartment()"
                class="rounded-xl border border-admin-line/10 bg-slate-950/20 p-3"
            >
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-white/55">Facility Department</p>
                        <p class="mt-1 text-xs text-white/40">Facilities images are stored under their department folder.</p>
                    </div>
                    <select
                        x-model="selectedDepartment"
                        class="h-10 w-full rounded-xl border border-[#30384a] bg-slate-950 px-3 text-sm text-white focus:border-admin-gold focus:outline-none focus:ring-2 focus:ring-admin-gold/20 sm:max-w-xs"
                    >
                        <option value="">Select department</option>
                        <template x-for="department in departments" :key="department.id">
                            <option :value="department.id" x-text="`${department.name} (#${department.id})`"></option>
                        </template>
                    </select>
                </div>
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
</div>
@endsection
