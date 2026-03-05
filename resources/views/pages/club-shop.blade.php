@extends('layouts.app')
@section('title', 'Club Shop — Chittagong Club Ltd.')
@section('show_nav', true)

@section('content')
<div
    x-data="{
        activeCategory: 'All Items',
        categories: ['All Items', 'Apparel', 'Accessories', 'Souvenirs'],
        cart: [],
        products: [
            { id:1, name:'Premium Club Blazer',  price:12500, category:'Apparel',     photo:'https://lh3.googleusercontent.com/aida-public/AB6AXuA2i6eg26VXopFyVAD-YOzGYlrgYI7kKcofshjIXo69MIXzjHFXdhUdxDQMEz2Guzb-ey1etn4FWnpF6G5TKRw_WaxpZoMxJdEI94Yi0XPXx4P1GZ1tK7EVINHTnN08jQjDO_yTNRG-lzKFaskMdCZ0_LeaagbYhn99TGeLpULu2Q8U5tmAeZyO8IvOqUZ8EoFIGqv0p_olKcXWcS1LgassBpKLkSX-uyuHkMNCZIfl_fHfXXmwiRZuQPkme0XBZNrZWJSKT-cA6DM' },
            { id:2, name:'Gold Crest Polo',       price:3200,  category:'Apparel',     photo:'https://lh3.googleusercontent.com/aida-public/AB6AXuA-9u7yaPShfhpZdgkjQKuhcEw1TLFrdCKft8U89yVmF4L8CH6YBnPWKn2rah8m8Iuv314GsJy0x7t48UhHtsLPLzHe-7d3meou4oU9l9xe9UoHFVqQUy_tE-SDLjlzrMMhJU7-CRHjXXJJJ8g7ctbAEB4VhdH3cu8FmxYHSZXaeBAdWgIl6fdL3Y-_7XNE0bKbUB7XmWuwP8-b1ABeeNmDM-To_sK6UW1e_9M7Bbi-SSU1ST7ir5B-Z1f4D4d_Jvjyb8kGZ2P0Aco' },
            { id:3, name:'Signature Silk Tie',    price:2500,  category:'Accessories', photo:'https://lh3.googleusercontent.com/aida-public/AB6AXuC9Qf3LqhZDh_S3FwOc0zLPFnvK4xYIHQjdJkhp1pnFxKxzk2lOK8kS71BmmmfJ96ZLM2I4Jx9NzKqqku4YoIz-DrFbGNG_ikodVfunnZo7jaV0tTKR32b_Pwdg8YOvV00uK_hU6057P72vqhs08LoRvE-0DAFfIgklpeigcBEZ67JfWT11yo_kOhOQYbBR08iGniya0EI0Q_jagnA7Kpi-ouh6E54TEIH7Efo1k28Xze20VsY6ZKw8Hzfw1SyN-VAI2rDSAs-1Aro' },
            { id:4, name:'Members\' Cap',          price:1800,  category:'Apparel',     photo:'https://lh3.googleusercontent.com/aida-public/AB6AXuDmSHfIU9XuBJ0-4XNUIsF3O4VGXFnHoqRgv0NdHXvT8r0HS5PHFkvTR85HGkqrJIydF21XVSdEVVVB-6jGAgdEsUAEN5UcAwrTLNDFdPRaZ8BN20oawRGe9YDpLotaarknHMl7pFlbpSn_5plw7AnXQc1RoPJ-sqWMYk75bnzxyufD-82KE8ozBJACbyaLa3R599lYqBoLQ75wKsKDgxXebXus-uSm70W7yXEISf3gTS32KUCm-veGP9lLzmXsgNcX2tYyPKG8R5A' },
        ],
        get filtered() {
            if (this.activeCategory === 'All Items') return this.products;
            return this.products.filter(p => p.category === this.activeCategory);
        },
        addToCart(product) {
            const existing = this.cart.find(i => i.id === product.id);
            if (existing) { existing.qty++; } else { this.cart.push({ ...product, qty: 1 }); }
        },
        get cartCount() { return this.cart.reduce((s, i) => s + i.qty, 0); },
        get cartTotal() { return this.cart.reduce((s, i) => s + i.price * i.qty, 0); },
        formatPrice(p) { return '৳ ' + p.toLocaleString('en-BD'); },
    }"
    class="flex flex-col min-h-screen pb-24"
>
    {{-- Header --}}
    <header class="flex items-center justify-between px-6 pt-12 pb-4 bg-brand-blue sticky top-0 z-30">
        <div class="flex flex-col">
            <p class="text-[10px] uppercase tracking-[0.2em] text-primary/80 font-semibold">Established 1878</p>
            <h1 class="text-xl font-extrabold text-white tracking-tight">
                CCL <span class="text-primary">Shop</span>
            </h1>
        </div>
        <div class="flex items-center gap-3">
            <button class="flex items-center justify-center size-10 rounded-full bg-white/10 hover:bg-white/20 transition-colors">
                <span class="material-symbols-outlined text-primary text-[22px]">search</span>
            </button>
            <button class="relative flex items-center justify-center size-10 rounded-full bg-white/10 hover:bg-white/20 transition-colors">
                <span class="material-symbols-outlined text-primary text-[22px]">shopping_bag</span>
                <span
                    x-show="cartCount > 0"
                    x-text="cartCount"
                    class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-brand-blue"
                ></span>
            </button>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex-1 overflow-y-auto hide-scrollbar px-4 pb-40">

        {{-- Member Credit --}}
        <div class="mt-4 mb-6 px-2">
            <div class="flex items-center justify-between p-4 rounded-xl bg-white/5 border border-white/10">
                <div class="flex flex-col">
                    <span class="text-xs text-white/50">Welcome back,</span>
                    <span class="text-sm font-bold text-primary italic">Member #4291</span>
                </div>
                <div class="h-8 w-px bg-white/10 mx-4"></div>
                <div class="flex flex-col items-end">
                    <span class="text-xs text-white/50">Credit Balance</span>
                    <span class="text-sm font-bold text-white">৳ 45,200</span>
                </div>
            </div>
        </div>

        {{-- Category chips --}}
        <div class="flex gap-3 mb-8 overflow-x-auto hide-scrollbar py-2">
            <template x-for="cat in categories" :key="cat">
                <button
                    @click="activeCategory = cat"
                    :class="activeCategory === cat ? 'bg-primary text-brand-blue font-bold shadow-lg shadow-primary/20' : 'bg-white/5 border border-white/10 text-white/80'"
                    class="flex h-10 shrink-0 items-center justify-center rounded-full px-6 text-sm transition-all"
                    x-text="cat"
                ></button>
            </template>
        </div>

        {{-- Products --}}
        <div class="flex items-center justify-between mb-4 px-2">
            <h3 class="text-lg font-bold text-white tracking-wide">Signature Collection</h3>
            <span class="text-primary text-xs font-semibold uppercase tracking-widest border-b border-primary/30">View All</span>
        </div>

        <div class="grid grid-cols-2 gap-4 pb-10">
            <template x-for="product in filtered" :key="product.id">
                <div class="flex flex-col group">
                    <div class="relative w-full aspect-[4/5] rounded-xl overflow-hidden bg-white/5 border border-white/10 group-active:scale-95 transition-transform duration-200">
                        <div class="absolute inset-0 bg-cover bg-center" :style="`background-image: url('${product.photo}')`"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <button
                            @click="addToCart(product)"
                            class="absolute bottom-3 right-3 flex size-10 items-center justify-center rounded-lg gold-gradient shadow-lg text-brand-blue hover:scale-110 active:scale-90 transition-all"
                        >
                            <span class="material-symbols-outlined text-[20px]">add_shopping_cart</span>
                        </button>
                    </div>
                    <div class="mt-3 px-1">
                        <p class="text-white text-[15px] font-semibold leading-tight mb-1 truncate" x-text="product.name"></p>
                        <p class="text-primary text-sm font-bold" x-text="formatPrice(product.price)"></p>
                    </div>
                </div>
            </template>
        </div>

    </main>

    {{-- Checkout Bar --}}
    <div
        x-show="cartCount > 0"
        x-transition
        class="absolute bottom-20 left-0 right-0 p-6 bg-gradient-to-t from-brand-blue via-brand-blue to-transparent pt-12 pb-4 z-40"
    >
        <div class="flex items-end justify-between mb-4 px-2">
            <div class="flex flex-col">
                <span class="text-white/60 text-[11px] uppercase tracking-wider">Total Amount</span>
                <span class="text-2xl font-extrabold text-white" x-text="formatPrice(cartTotal)"></span>
            </div>
            <span class="text-xs text-primary/80 mb-1" x-text="cartCount + ' Item(s) Selected'"></span>
        </div>
        <button class="w-full py-4 rounded-xl gold-gradient text-brand-blue font-bold text-lg shadow-[0_10px_30px_rgba(242,208,13,0.3)] flex items-center justify-center gap-3 active:scale-[0.98] transition-all">
            <span>Checkout Now</span>
            <span class="material-symbols-outlined">arrow_forward_ios</span>
        </button>
    </div>

</div>
@endsection
