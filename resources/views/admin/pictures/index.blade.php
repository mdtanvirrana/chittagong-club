@extends('layouts.admin')

@section('page_title', 'Picture Folders')
@section('page_eyebrow', 'Media')

@section('content')
<div class="space-y-4">
    <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
        <div class="flex flex-col gap-4 border-b border-admin-line/10 pb-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">Folders</p>
                <p class="mt-1 text-sm text-white/45">Choose a folder to browse its uploaded images.</p>
            </div>

            <a
                href="{{ route('admin.pictures.create') }}"
                class="inline-flex h-10 items-center justify-center rounded-xl border border-admin-gold/20 bg-admin-gold px-4 text-sm font-semibold text-admin-ink transition hover:brightness-105"
            >
                Upload Pictures
            </a>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
            @foreach ($folderSummaries as $folderItem)
                <a
                    href="{{ route('admin.pictures.index', ['folder' => $folderItem['folder']]) }}"
                    class="group flex min-h-[7rem] flex-col items-center justify-center gap-2 rounded-2xl border px-3 py-4 text-center transition {{ $selectedFolder === $folderItem['folder'] ? 'border-admin-gold bg-admin-gold/10' : 'border-admin-line/10 bg-slate-950/20 hover:border-admin-line/30 hover:bg-white/[0.04]' }}"
                >
                    <span class="material-symbols-outlined text-[2.3rem] leading-none {{ $selectedFolder === $folderItem['folder'] ? 'text-admin-gold' : 'text-white/72 group-hover:text-white' }}">
                        {{ $selectedFolder === $folderItem['folder'] ? 'folder_open' : 'folder' }}
                    </span>
                    <span class="max-w-[8rem] text-xs font-semibold leading-snug text-white">{{ $folderItem['folder_label'] }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
        @if ($selectedFolder)
            <div class="flex flex-col gap-4 border-b border-admin-line/10 pb-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">Images</p>
                    <p class="mt-1 text-sm font-semibold text-white">{{ \App\Support\PortalImageDirectory::labelForFolder($selectedFolder) }}</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <form method="GET" action="{{ route('admin.pictures.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <input type="hidden" name="folder" value="{{ $selectedFolder }}">
                        <div class="relative">
                            <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base text-white/35">search</span>
                            <input
                                type="search"
                                name="q"
                                value="{{ $search }}"
                                placeholder="Search image name"
                                class="h-10 w-full min-w-[16rem] rounded-xl border border-[#30384a] bg-white/[0.04] pl-10 pr-3 text-sm text-white focus:border-admin-gold focus:outline-none focus:ring-2 focus:ring-admin-gold/20"
                            >
                        </div>
                        <button type="submit" class="inline-flex h-10 items-center justify-center rounded-xl border border-[#30384a] px-4 text-sm font-medium text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                            Search
                        </button>
                        @if ($search !== '')
                            <a href="{{ route('admin.pictures.index', ['folder' => $selectedFolder]) }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-[#30384a] px-4 text-sm font-medium text-white/55 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                                Clear
                            </a>
                        @endif
                    </form>

                    <p class="text-xs text-white/45">
                        @if ($pictures->total() > 0)
                            {{ $pictures->firstItem() }}-{{ $pictures->lastItem() }} of {{ $pictures->total() }}
                        @else
                            0 shown
                        @endif
                    </p>
                </div>
            </div>

            @if ($pictures->isEmpty())
                <div class="px-4 py-12 text-center text-sm text-white/45">
                    @if ($search !== '')
                        No images matched `{{ $search }}` in this folder.
                    @else
                        No image files found in this folder.
                    @endif
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
                                        <input type="hidden" name="relative_path" value="{{ $picture['relative_path'] }}">
                                        <input type="hidden" name="page" value="{{ $pictures->currentPage() }}">
                                        <input type="hidden" name="folder" value="{{ $selectedFolder }}">
                                        <input type="hidden" name="q" value="{{ $search }}">
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
        @else
            <div class="px-4 py-12 text-center text-sm text-white/45">
                Select a folder above to view its uploaded images.
            </div>
        @endif
    </section>
</div>
@endsection
