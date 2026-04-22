@extends('layouts.userpanel')
@section('page_title', 'Gallery')
@section('show_nav', true)
@section('userpanel_content')

    <div x-data="gallery()" class="flex flex-col min-h-screen pb-24">

        {{-- Album list --}}
        <main class="px-4 py-5 space-y-4">
            <template x-for="album in albums" :key="album.id">
                <div>
                    {{-- Album card — cover + title --}}
                    <button
                        @click="toggleAlbum(album.id)"
                        class="w-full text-left group active:scale-[0.98] transition-transform"
                    >
                        <div class="relative rounded-2xl overflow-hidden border border-white/10">
                            {{-- Cover image --}}
                            <img :src="album.cover" :alt="album.title"
                                 class="w-full h-44 object-cover">
                            {{-- Overlay --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                            {{-- Info --}}
                            <div class="absolute bottom-0 left-0 right-0 p-4 flex items-end justify-between">
                                <div>
                                    <p class="text-white font-extrabold text-base leading-tight" x-text="album.title"></p>
                                    <p class="text-white/50 text-xs mt-0.5" x-text="album.photos.length + ' photos • ' + album.date"></p>
                                </div>
                                <div class="size-9 rounded-full bg-white/10 border border-white/20 flex items-center justify-center shrink-0 transition-transform duration-300"
                                     :class="openId === album.id ? 'rotate-180' : ''">
                                    <span class="material-symbols-outlined text-white text-lg">expand_more</span>
                                </div>
                            </div>
                        </div>
                    </button>

                    {{-- Photo grid — expands inline --}}
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
            window.gallery = function() {
                return {
                    openId: null,
                    lightbox: null,
                    lightboxPhotos: [],
                    lightboxIndex: 0,

                    albums: [
                        {
                            id: 1,
                            title: "CCL Annual Picnic 2025 — Cox's Bazar",
                            date: "Jul 2025",
                            cover: "https://chittagongclubltd.com/wp-content/uploads/516397458_1195829472344686_8600613007741992270_n-390x220.jpg",
                            photos: [
                                "https://chittagongclubltd.com/wp-content/uploads/514412334_1195829789011321_3231356427679711645_n-1024x682-640x480.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/515175308_1195830595677907_2531546707026841259_n-1024x682-640x480.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/514412334_1195831355677831_974566463758530987_n-1024x682-640x480.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/515232860_1195831719011128_3129229813544659165_n-1024x682-640x480.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/517381013_1195829859011314_5597147289238224683_n-1024x682-640x480.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/518297939_1195829649011335_1047892657090169450_n-1024x682-640x480.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/515491609_1195830772344556_2114906883371593876_n-1024x768-640x480.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/518385492_1195829769011323_3734471175605333111_n-1024x682-640x480.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/516579961_1195831119011188_4399786127708897658_n-1024x682-640x480.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/514409296_1195831372344496_336865239325453365_n-1024x682-640x480.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/514408482_1195831439011156_2804052093269747519_n-1024x682-640x480.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/515492122_1195830702344563_8149524238491161159_n-1024x682-640x480.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/515045389_1195830069011293_1414580954621185984_n-1024x682-640x480.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/515493948_1195830839011216_1197932720317221838_n-1024x682-640x480.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/515206297_1195830585677908_3846263581164435847_n-1024x682-640x480.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/516131888_1195831082344525_5641537397640418625_n-1024x682-640x480.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/516339219_1195830809011219_5102337185137399186_n-1024x682-640x480.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/516394143_1195831042344529_7493277881091328965_n-1024x682-640x480.jpg"
                            ]
                        },
                        {
                            id: 2,
                            title: "Visit of Banani Club — May 2025",
                            date: "May 2025",
                            cover: "https://chittagongclubltd.com/wp-content/uploads/499475559_1157779032816397_8322369307345940727_n-390x220.jpg",
                            photos: [
                                "https://chittagongclubltd.com/wp-content/uploads/499475559_1157779032816397_8322369307345940727_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/500432294_1162029769057990_2963475746338765386_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/2025/05/491964658_1132838425310458_8023127850489107116_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/2025/05/491911112_1136555144938786_1904903039340596486_n-390x220.jpg"
                            ]
                        },
                        {
                            id: 3,
                            title: "CCL Visit to Gulshan Club — 2025",
                            date: "Jun 2025",
                            cover: "https://chittagongclubltd.com/wp-content/uploads/500432294_1162029769057990_2963475746338765386_n-390x220.jpg",
                            photos: [
                                "https://chittagongclubltd.com/wp-content/uploads/500432294_1162029769057990_2963475746338765386_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/499475559_1157779032816397_8322369307345940727_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/2025/05/491964658_1132838425310458_8023127850489107116_n-390x220.jpg"
                            ]
                        },
                        {
                            id: 4,
                            title: "পহেলা বৈশাখ ১৪৩২ উদযাপন",
                            date: "Apr 2025",
                            cover: "https://chittagongclubltd.com/wp-content/uploads/2025/05/491964658_1132838425310458_8023127850489107116_n-390x220.jpg",
                            photos: [
                                "https://chittagongclubltd.com/wp-content/uploads/2025/05/491964658_1132838425310458_8023127850489107116_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/2025/05/491911112_1136555144938786_1904903039340596486_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/485120747_1112033364057631_1449054655296440854_n-390x220.jpg"
                            ]
                        },
                        {
                            id: 5,
                            title: "Visit to Dhaka Club — April 2025",
                            date: "Apr 2025",
                            cover: "https://chittagongclubltd.com/wp-content/uploads/2025/05/491911112_1136555144938786_1904903039340596486_n-390x220.jpg",
                            photos: [
                                "https://chittagongclubltd.com/wp-content/uploads/2025/05/491911112_1136555144938786_1904903039340596486_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/2025/05/491964658_1132838425310458_8023127850489107116_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/485120747_1112033364057631_1449054655296440854_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/500432294_1162029769057990_2963475746338765386_n-390x220.jpg"
                            ]
                        },
                        {
                            id: 6,
                            title: "31st Night Celebration 2025",
                            date: "Jan 2025",
                            cover: "https://chittagongclubltd.com/wp-content/uploads/485120747_1112033364057631_1449054655296440854_n-390x220.jpg",
                            photos: [
                                "https://chittagongclubltd.com/wp-content/uploads/485120747_1112033364057631_1449054655296440854_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/516397458_1195829472344686_8600613007741992270_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/2024/05/428391580_851753763418927_7037551204923717324_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/2024/05/416105179_826920959235541_57715062360889874_n-390x220.jpg"
                            ]
                        },
                        {
                            id: 7,
                            title: "Valentine's Day Party 2024",
                            date: "Feb 2024",
                            cover: "https://chittagongclubltd.com/wp-content/uploads/2024/05/428391580_851753763418927_7037551204923717324_n-390x220.jpg",
                            photos: [
                                "https://chittagongclubltd.com/wp-content/uploads/2024/05/428391580_851753763418927_7037551204923717324_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/2024/05/416105179_826920959235541_57715062360889874_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/2023/10/387111619_776065810987723_7611858033778248782_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/2023/10/362679083_738365984757706_6455087635969311014_n-390x220.jpg"
                            ]
                        },
                        {
                            id: 8,
                            title: "31st Night Celebration Party 2024",
                            date: "Dec 2023",
                            cover: "https://chittagongclubltd.com/wp-content/uploads/2024/05/416105179_826920959235541_57715062360889874_n-390x220.jpg",
                            photos: [
                                "https://chittagongclubltd.com/wp-content/uploads/2024/05/416105179_826920959235541_57715062360889874_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/2024/05/428391580_851753763418927_7037551204923717324_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/485120747_1112033364057631_1449054655296440854_n-390x220.jpg"
                            ]
                        },
                        {
                            id: 9,
                            title: "CCL Soccer Group Tour — Cox's Bazar 2023",
                            date: "Oct 2023",
                            cover: "https://chittagongclubltd.com/wp-content/uploads/2023/10/387111619_776065810987723_7611858033778248782_n-390x220.jpg",
                            photos: [
                                "https://chittagongclubltd.com/wp-content/uploads/2023/10/387111619_776065810987723_7611858033778248782_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/2023/10/362679083_738365984757706_6455087635969311014_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/2024/05/428391580_851753763418927_7037551204923717324_n-390x220.jpg"
                            ]
                        },
                        {
                            id: 10,
                            title: "Eid Ul Adha Reunion 2023",
                            date: "Oct 2023",
                            cover: "https://chittagongclubltd.com/wp-content/uploads/2023/10/362679083_738365984757706_6455087635969311014_n-390x220.jpg",
                            photos: [
                                "https://chittagongclubltd.com/wp-content/uploads/2023/10/362679083_738365984757706_6455087635969311014_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/2023/10/387111619_776065810987723_7611858033778248782_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/2024/05/416105179_826920959235541_57715062360889874_n-390x220.jpg",
                                "https://chittagongclubltd.com/wp-content/uploads/485120747_1112033364057631_1449054655296440854_n-390x220.jpg"
                            ]
                        }
                    ],

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
