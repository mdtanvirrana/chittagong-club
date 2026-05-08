@extends('layouts.userpanel')
@section('page_title', 'Gallery')
@section('show_nav', true)
@section('userpanel_content')

    <div x-data="gallery(@js($albums->values()))" class="flex flex-col min-h-screen pb-24">

        {{-- Album list --}}
        <main class="px-4 py-5 space-y-4">
            @if ($albums->isEmpty())
                <div class="rounded-2xl border border-primary/20 bg-primary/5 px-5 py-10 text-center">
                    <span class="material-symbols-outlined text-5xl text-primary/80">photo_library</span>
                    <h2 class="mt-3 text-lg font-bold text-white">No gallery albums found</h2>
                    <p class="mt-2 text-sm text-white/55">Albums uploaded from the admin panel will appear here.</p>
                </div>
            @endif

            <template x-for="album in albums" :key="album.id">
                <div>
                    {{-- Album card - cover + title --}}
                    <button
                        @click="toggleAlbum(album.id)"
                        class="w-full text-left group active:scale-[0.98] transition-transform"
                    >
                        <div class="relative rounded-2xl overflow-hidden border border-white/10">
                            {{-- Cover image --}}
                            <img :src="album.cover" :alt="album.title"
                                 class="w-full h-44 object-cover"
                                 style="filter: brightness(0.82) contrast(1.08);">
                            {{-- Overlay --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-950/45 to-transparent"></div>
                            {{-- Info --}}
                            <div class="absolute bottom-0 left-0 right-0 p-3">
                                <div class="flex items-end justify-between gap-3 rounded-2xl border border-white/80 bg-white px-4 py-3"
                                     style="box-shadow: 0 18px 36px -24px rgba(15, 23, 42, 0.55);">
                                    <div class="min-w-0">
                                        <p class="text-black font-extrabold text-base leading-tight"
                                           x-text="album.title"></p>
                                        <p class="text-black text-xs mt-1 font-semibold"
                                           x-text="album.photos.length + ' photos • ' + album.date"></p>
                                    </div>
                                    <div class="size-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 transition-transform duration-300 shadow-sm"
                                         :class="openId === album.id ? 'rotate-180' : ''">
                                        <span class="material-symbols-outlined text-black text-lg">expand_more</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </button>

                    {{-- Photo grid - expands inline --}}
                    <div x-show="openId === album.id"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="mt-2 grid grid-cols-3 gap-1.5">
                        <template x-for="(photo, idx) in album.photos" :key="idx">
                            <button @click="openLightbox(album.photos, idx)"
                                    class="aspect-square rounded-xl overflow-hidden active:scale-95 transition-transform">
                                <img :src="photo" class="w-full h-full object-cover" loading="lazy">
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </main>

        {{-- Lightbox --}}
        <template x-if="lightbox !== null">
            <div class="fixed inset-0 z-[100] bg-black/95 flex flex-col"
                 @keydown.escape.window="lightbox = null"
                 @keydown.arrow-left.window="prevPhoto()"
                 @keydown.arrow-right.window="nextPhoto()">

                {{-- Top bar --}}
                <div class="flex items-center justify-between px-4 py-4 shrink-0">
                    <button @click="lightbox = null"
                            class="flex size-9 items-center justify-center rounded-full bg-white/10">
                        <span class="material-symbols-outlined text-white text-lg">close</span>
                    </button>
                    <p class="text-white/50 text-sm"
                       x-text="(lightboxIndex + 1) + ' / ' + lightboxPhotos.length"></p>
                    <div class="size-9"></div>
                </div>

                {{-- Image --}}
                <div class="flex-1 flex items-center justify-center px-4 relative">
                    <button @click="prevPhoto()"
                            class="absolute left-2 z-10 size-10 flex items-center justify-center rounded-full bg-white/10 active:bg-white/20"
                            x-show="lightboxIndex > 0">
                        <span class="material-symbols-outlined text-white">chevron_left</span>
                    </button>

                    <img :src="lightboxPhotos[lightboxIndex]"
                         class="max-h-full max-w-full rounded-xl object-contain"
                         :key="lightboxIndex">

                    <button @click="nextPhoto()"
                            class="absolute right-2 z-10 size-10 flex items-center justify-center rounded-full bg-white/10 active:bg-white/20"
                            x-show="lightboxIndex < lightboxPhotos.length - 1">
                        <span class="material-symbols-outlined text-white">chevron_right</span>
                    </button>
                </div>

                {{-- Thumbnails --}}
                <div class="flex gap-2 px-4 py-4 overflow-x-auto hide-scrollbar shrink-0">
                    <template x-for="(photo, idx) in lightboxPhotos" :key="idx">
                        <button @click="lightboxIndex = idx"
                                class="shrink-0 size-14 rounded-lg overflow-hidden border-2 transition-all"
                                :class="lightboxIndex === idx ? 'border-primary' : 'border-transparent opacity-50'">
                            <img :src="photo" class="w-full h-full object-cover">
                        </button>
                    </template>
                </div>
            </div>
        </template>

    </div>

    <script>
        (function() {
            window.gallery = function(uploadedAlbums) {
                return {
                    openId: uploadedAlbums && uploadedAlbums.length > 0 ? uploadedAlbums[0].id : null,
                    lightbox: null,
                    lightboxPhotos: [],
                    lightboxIndex: 0,
                    albums: uploadedAlbums || [],

                    toggleAlbum: function(id) {
                        this.openId = this.openId === id ? null : id;
                    },

                    openLightbox: function(photos, idx) {
                        this.lightboxPhotos = photos;
                        this.lightboxIndex  = idx;
                        this.lightbox       = true;
                    },

                    prevPhoto: function() {
                        if (this.lightboxIndex > 0) this.lightboxIndex--;
                    },

                    nextPhoto: function() {
                        if (this.lightboxIndex < this.lightboxPhotos.length - 1) this.lightboxIndex++;
                    }
                };
            };
        })();
    </script>

@endsection
