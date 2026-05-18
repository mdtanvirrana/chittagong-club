@extends('layouts.admin')

@section('page_title', 'Gallery Albums')
@section('page_eyebrow', 'Media')

@section('content')
<div class="space-y-4">
    @if ($errors->any())
        <section class="rounded-lg border border-red-400/25 bg-red-500/10 p-4 text-sm text-red-100">
            {{ $errors->first() }}
        </section>
    @endif

    <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
        <div class="flex flex-col gap-4 border-b border-admin-line/10 pb-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">Albums</p>
                <p class="mt-1 text-sm text-white/45">Browse gallery folders and manage album names, images, and deletion.</p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <a
                    href="{{ route('gallery') }}"
                    target="_blank"
                    rel="noreferrer"
                    class="inline-flex h-10 items-center justify-center rounded-xl border border-[#30384a] px-4 text-sm font-medium text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]"
                >
                    View Member Gallery
                </a>
                <a
                    href="{{ route('admin.gallery.create') }}"
                    class="inline-flex h-10 items-center justify-center rounded-xl border border-admin-gold/20 bg-admin-gold px-4 text-sm font-semibold text-admin-ink transition hover:brightness-105"
                >
                    Upload Gallery
                </a>
            </div>
        </div>

        @if ($albums->isEmpty())
            <div class="px-4 py-12 text-center text-sm text-white/45">
                No gallery albums uploaded yet.
            </div>
        @else
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                @foreach ($albums as $album)
                    <a
                        href="{{ route('admin.gallery.index', ['album' => $album['id']]) }}"
                        class="group overflow-hidden rounded-lg border transition {{ $selectedAlbumId === $album['id'] ? 'border-admin-gold bg-admin-gold/10' : 'border-admin-line/10 bg-slate-950/20 hover:border-admin-line/30 hover:bg-white/[0.04]' }}"
                    >
                        <div class="aspect-[16/9] overflow-hidden bg-slate-950/40">
                            @if ($album['cover'])
                                <img src="{{ $album['cover'] }}" alt="{{ $album['title'] }}" class="h-full w-full object-cover transition group-hover:scale-[1.02]">
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl text-white/35">photo_library</span>
                                </div>
                            @endif
                        </div>
                        <div class="p-3">
                            <p class="truncate text-sm font-semibold text-white">{{ $album['title'] }}</p>
                            <p class="mt-1 text-xs text-white/45">{{ $album['photo_count'] }} photo(s) - {{ $album['date'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
        @if ($selectedAlbum)
            <div class="flex flex-col gap-4 border-b border-admin-line/10 pb-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">Selected Album</p>
                    <p class="mt-1 text-sm font-semibold text-white">{{ $selectedAlbum['title'] }}</p>
                </div>

                <form method="POST" action="{{ route('admin.gallery.destroy', $selectedAlbum['id']) }}" onsubmit="return confirm('Delete this gallery album and all of its images?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex h-10 items-center justify-center rounded-xl border border-red-400/25 px-4 text-sm font-medium text-red-200 transition hover:border-red-300/40 hover:bg-red-500/10">
                        Delete Album
                    </button>
                </form>
            </div>

            <div class="mt-4 rounded-lg border border-admin-line/10 bg-slate-950/20 p-3">
                <form method="POST" action="{{ route('admin.gallery.update', $selectedAlbum['id']) }}" class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="album_name" class="block text-xs font-semibold uppercase tracking-[0.16em] text-white/55">Album Name</label>
                        <input
                            id="album_name"
                            name="album_name"
                            type="text"
                            value="{{ old('album_name', $selectedAlbum['title']) }}"
                            maxlength="120"
                            class="mt-2 h-11 w-full rounded-xl border border-[#30384a] bg-white/[0.04] px-3 text-sm text-white placeholder:text-white/30 focus:border-admin-gold focus:outline-none focus:ring-2 focus:ring-admin-gold/20"
                        >
                    </div>
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl border border-admin-gold/20 bg-admin-gold px-5 text-sm font-semibold text-admin-ink transition hover:brightness-105">
                        Save Album
                    </button>
                </form>
            </div>

            <div class="mt-4">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <p class="text-xs font-medium uppercase tracking-[0.16em] text-white/55">{{ $selectedAlbum['photo_count'] }} image(s)</p>
                    <a href="{{ route('admin.gallery.create') }}" class="inline-flex h-9 items-center justify-center rounded-xl border border-[#30384a] px-3 text-xs font-medium text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                        Add Images
                    </a>
                </div>

                @if (empty($selectedAlbum['photos']))
                    <div class="px-4 py-12 text-center text-sm text-white/45">
                        No images found in this album.
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                        @foreach ($selectedAlbum['photos'] as $photo)
                            <article class="overflow-hidden rounded-lg border border-admin-line/10 bg-slate-950/20">
                                <div class="aspect-square overflow-hidden bg-slate-950/40">
                                    <img src="{{ $photo['url'] }}" alt="{{ $photo['filename'] }}" class="h-full w-full object-cover">
                                </div>
                                <div class="space-y-2 p-3">
                                    <div>
                                        <p class="truncate text-xs font-semibold text-white">{{ $photo['filename'] }}</p>
                                        <p class="mt-1 text-xs text-white/45">{{ $photo['size_kb'] }} KB - {{ $photo['updated_at'] }}</p>
                                    </div>

                                    <form method="POST" action="{{ route('admin.gallery.images.update', [$selectedAlbum['id'], $photo['filename']]) }}" enctype="multipart/form-data" class="space-y-2">
                                        @csrf
                                        @method('PATCH')
                                        <input
                                            name="image_name"
                                            type="text"
                                            value="{{ old('image_name', $photo['name']) }}"
                                            maxlength="120"
                                            class="h-9 w-full rounded-xl border border-[#30384a] bg-white/[0.04] px-3 text-xs text-white placeholder:text-white/30 focus:border-admin-gold focus:outline-none focus:ring-2 focus:ring-admin-gold/20"
                                        >
                                        <input
                                            name="image_file"
                                            type="file"
                                            accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif"
                                            class="block w-full text-[11px] text-white/55 file:mr-3 file:h-8 file:rounded-lg file:border-0 file:bg-white/[0.08] file:px-3 file:text-[11px] file:font-medium file:text-white/72 hover:file:bg-white/[0.12]"
                                        >
                                        <div class="grid grid-cols-3 gap-1.5">
                                            <a href="{{ $photo['url'] }}" target="_blank" rel="noreferrer" class="inline-flex h-8 items-center justify-center border border-[#30384a] px-2 text-[11px] text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                                                Open
                                            </a>
                                            <button type="submit" class="inline-flex h-8 items-center justify-center border border-admin-gold/25 px-2 text-[11px] text-admin-gold transition hover:bg-admin-gold/10">
                                                Save
                                            </button>
                                            <button
                                                type="submit"
                                                form="delete-gallery-image-{{ $selectedAlbum['id'] }}-{{ md5($photo['filename']) }}"
                                                class="inline-flex h-8 items-center justify-center border border-red-400/25 px-2 text-[11px] text-red-200 transition hover:border-red-300/40 hover:bg-red-500/10"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </form>

                                    <form id="delete-gallery-image-{{ $selectedAlbum['id'] }}-{{ md5($photo['filename']) }}" method="POST" action="{{ route('admin.gallery.images.destroy', [$selectedAlbum['id'], $photo['filename']]) }}" onsubmit="return confirm('Delete this gallery image?')" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            <div class="px-4 py-12 text-center text-sm text-white/45">
                Select an album above to edit its name or manage images.
            </div>
        @endif
    </section>
</div>
@endsection
