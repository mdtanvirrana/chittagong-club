@extends('layouts.app')
@section('title', 'Club Facilities — Chittagong Club Ltd.')
@section('show_nav', true)

@push('styles')
<style>
    .gold-overlay {
        background: linear-gradient(0deg, rgba(12,92,139,0.9) 0%, rgba(242,185,13,0.2) 100%);
    }
</style>
@endpush

@section('content')
<div
    x-data="{
        activeFilter: 'All',
        filters: ['All', 'Dining', 'Sports', 'Wellness', 'Suites']
    }"
    class="flex flex-col min-h-screen pb-24"
>
    {{-- Sticky Header --}}
    <header class="sticky top-0 z-50 bg-background-dark/80 ios-blur border-b border-primary/20">
        <div class="flex items-center justify-between px-4 h-16">
            <a href="{{ route('dashboard') }}" class="flex items-center justify-center p-2 rounded-full hover:bg-primary/10 transition-colors">
                <span class="material-symbols-outlined text-primary">arrow_back_ios</span>
            </a>
            <div class="flex flex-col items-center">
                <span class="text-[10px] uppercase tracking-[0.2em] text-primary font-bold">Chittagong Club Ltd</span>
                <h1 class="text-lg font-bold leading-tight">Club Facilities</h1>
            </div>
            <div class="p-2 rounded-full hover:bg-primary/10 transition-colors">
                <span class="material-symbols-outlined text-primary">search</span>
            </div>
        </div>

        {{-- Filter Chips --}}
        <div class="flex gap-3 px-4 pb-4 overflow-x-auto hide-scrollbar mt-2">
            <template x-for="f in filters" :key="f">
                <button
                    @click="activeFilter = f"
                    :class="activeFilter === f
                        ? 'bg-primary text-background-dark font-bold shadow-lg shadow-primary/20'
                        : 'bg-primary/10 border border-primary/30 text-primary'"
                    class="flex h-9 shrink-0 items-center justify-center gap-x-2 rounded-full px-5 text-sm transition-all"
                    x-text="f"
                ></button>
            </template>
        </div>
    </header>

    {{-- Gallery Grid --}}
    <main class="flex-1 p-4 mb-4">
        <div class="grid grid-cols-2 gap-4">

            {{-- Grand Library --}}
            <div class="relative group aspect-[3/4] rounded-xl overflow-hidden border border-primary/20">
                <div class="absolute inset-0 bg-cover bg-center"
                     style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCFCwSPOiJIzQOtsAOAwPvR2Zb1W_UC35d7foSRMrTCgJyg1-XLgfvFySTPft76gXtRWg61yz2dTCgA_-89hEbIbUXMBkH4T5CcfKG5ZwZLm8ABKZ2sgirSrVFs1Uxrq-kZCv2H9c9VGH9VyYPbIWQEKLImwR-hR8VVlol_r6R73u_sVKvBebBYCpURqxaRc-gpd_Qg7yrJEsqm91cWkhgRBxu8EAVK2AD-kWSBIB_EPd3h5UFjGYZd4Y64vkDH0bAFgzykGpXtoqI')">
                </div>
                <div class="absolute inset-0 gold-overlay opacity-60 group-active:opacity-80 transition-opacity"></div>
                <div class="absolute inset-0 flex flex-col justify-end p-4">
                    <span class="material-symbols-outlined text-primary text-3xl mb-2">menu_book</span>
                    <h3 class="text-white text-lg font-bold leading-tight">Grand Library</h3>
                    <p class="text-primary/80 text-xs font-medium uppercase tracking-wider mt-1">Lounge</p>
                </div>
            </div>

            {{-- Olympic Pool --}}
            <div class="relative group aspect-[3/4] rounded-xl overflow-hidden border border-primary/20">
                <div class="absolute inset-0 bg-cover bg-center"
                     style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDkfTysTe4Qqqlb0c85H5rgOx-FWefaK4lQha4Yc9W7cHnU2UvyHCNM4kKmdiDOZpGQ-lJBabJMw1veFF8484IEPbuVLJ7Te7s_GaBGiK0JlyfXCTg4mQDdAEUkrb6Q3c1qrh5lOFTnAqmHx1K36ieaa6vzc0D5bYSnH9ia37iyCdMGG12qI2fPDmoXQHq8kEpGiopHQA8pcV8497Nj6m5DVzk6KOvubgVKHbS39fO7SC1RIBHKB9_-t4Qot8VdhzbgxABW5g2OlK0')">
                </div>
                <div class="absolute inset-0 gold-overlay opacity-60 group-active:opacity-80 transition-opacity"></div>
                <div class="absolute inset-0 flex flex-col justify-end p-4">
                    <span class="material-symbols-outlined text-primary text-3xl mb-2">pool</span>
                    <h3 class="text-white text-lg font-bold leading-tight">Olympic Pool</h3>
                    <p class="text-primary/80 text-xs font-medium uppercase tracking-wider mt-1">Aquatics</p>
                </div>
            </div>

            {{-- Main Dining - full width --}}
            <div class="relative group aspect-square col-span-2 rounded-xl overflow-hidden border border-primary/20">
                <div class="absolute inset-0 bg-cover bg-center"
                     style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuB4SPtK0dHOiCkmy1djwk3vG_Oa6yDYUJ8b1zeIROksyMDrdsg4MQRPxgWJtOXjdsfZDz1vKZVRmM9IJ5_R23437Rnp26xR2Gf7dAalAXh2XANqScfzvBdeHR7BlUB9R2kwbCcn6wQwCPD-nBGHSjIb_OaTwza_G_BoTzFIOWR6tw0mgxWNl8nHDC61SYSWtsw_FaPxOLX7stvKn9Mbp29PZw80OuNZJV0hfg0JK3-OL442dor3qML_f46zX3i6E10_Fq3X2CbWIMc')">
                </div>
                <div class="absolute inset-0 gold-overlay opacity-50 group-active:opacity-70 transition-opacity"></div>
                <div class="absolute inset-0 flex flex-col justify-end p-6">
                    <span class="material-symbols-outlined text-primary text-4xl mb-2">restaurant</span>
                    <h3 class="text-white text-2xl font-bold leading-tight">The Imperial Hall</h3>
                    <p class="text-primary/90 text-sm font-medium uppercase tracking-widest mt-1">Fine Dining</p>
                </div>
            </div>

            {{-- Fitness Centre --}}
            <div class="relative group aspect-[3/4] rounded-xl overflow-hidden border border-primary/20">
                <div class="absolute inset-0 bg-cover bg-center"
                     style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDeRghkWp5rEKKYl1cP2sHmvHLa7KhGDst5yVgMUy_noVv57fp9NGmiZ-z_6__okZO_cYrXBSezGjRhggwHwPhbBpAJPHAniGz6Q0QPQlFoU_XE0sASqfghO9_HzKVgSZ7022GONu7KBGk4JSlJDB6G9wpxL6vjNbd0RJnuqLAdjmEE_NcHD9mt7rhdEAPofnlQ2VIn7TCr8mDOW35IzNlc5glPWciDvt03guAY8TsmNBUYwvFwh9dmtNq0f2-yRUIoNPblNsACa8M')">
                </div>
                <div class="absolute inset-0 gold-overlay opacity-60 group-active:opacity-80 transition-opacity"></div>
                <div class="absolute inset-0 flex flex-col justify-end p-4">
                    <span class="material-symbols-outlined text-primary text-3xl mb-2">fitness_center</span>
                    <h3 class="text-white text-lg font-bold leading-tight">Fitness Center</h3>
                    <p class="text-primary/80 text-xs font-medium uppercase tracking-wider mt-1">Wellness</p>
                </div>
            </div>

            {{-- Tennis Courts --}}
            <div class="relative group aspect-[3/4] rounded-xl overflow-hidden border border-primary/20">
                <div class="absolute inset-0 bg-cover bg-center"
                     style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDQo5pTgSu8UZoHf-kk0ZPSmNRE5xG2ippyQwQpRScueKzTPv6a9v8eSdPJPjs5NzUVeK5cMNNHdi-Ql68MIlIJJRlF35xn7a8xD-V9MBB4yYMECDoxscyZ7C01_EaotfwenrQ0Q_KaRsQAc34B8CDNlgSDV03FFlInlJk46PTKwpSqlyyNiGKIE4FHqhFLe4wtZ68o87Qj1JIHmFGSwZFWX4rN2_IGa0lSnxKtmO6rfp4zEB0Y1IYvQcFOC-qOwEBltrFj9wX3XkA')">
                </div>
                <div class="absolute inset-0 gold-overlay opacity-60 group-active:opacity-80 transition-opacity"></div>
                <div class="absolute inset-0 flex flex-col justify-end p-4">
                    <span class="material-symbols-outlined text-primary text-3xl mb-2">sports_tennis</span>
                    <h3 class="text-white text-lg font-bold leading-tight">Tennis Courts</h3>
                    <p class="text-primary/80 text-xs font-medium uppercase tracking-wider mt-1">Sports</p>
                </div>
            </div>

            {{-- Presidential Suites - full width --}}
            <div class="relative group aspect-square col-span-2 rounded-xl overflow-hidden border border-primary/20">
                <div class="absolute inset-0 bg-cover bg-center"
                     style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDA3Qit1V0rrtFyq-kz23rTHaCAcLIlhu5Avkwx4-uzs7CFDnrGUHSqVg-sgBGdn9esInZf43x3kTxN_Y9WH0N6hZd1Vdrz27IJLQcdHlKE1V4gMVKPxJ47vnyP3VuGoWobTxfysI3qDVz6dr1H-TH7Cmb4M0_ZaZ3tmQziM9t1OhuhvcchteF0lwj_TGeR4QeWfQnzhL_Xe4Z_Iur9w2Y_yzI69bFpC5RDr1XUVuR2cXYqLLShEVu_e2So-rSgjhut25rI08bX9Jo')">
                </div>
                <div class="absolute inset-0 gold-overlay opacity-50 group-active:opacity-70 transition-opacity"></div>
                <div class="absolute inset-0 flex flex-col justify-end p-6">
                    <span class="material-symbols-outlined text-primary text-4xl mb-2">king_bed</span>
                    <h3 class="text-white text-2xl font-bold leading-tight">Presidential Suites</h3>
                    <p class="text-primary/90 text-sm font-medium uppercase tracking-widest mt-1">Guest Rooms</p>
                </div>
            </div>

        </div>
    </main>

</div>
@endsection
