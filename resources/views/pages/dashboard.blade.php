@extends('layouts.app')
@section('title', 'Dashboard — Chittagong Club Ltd.')
@section('show_nav', true)

@section('content')
<div class="flex flex-col min-h-screen pb-24">

    {{-- Sticky Top Bar --}}
    <header class="sticky top-0 z-50 flex items-center justify-between px-6 py-4 bg-brand-blue/90 ios-blur">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-full gold-border bg-center bg-cover overflow-hidden"
                 style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBWXnlPIa_0ZYvDnwj9fhUL7QtwKOykPMtr8tj4eYPNW83pcPsWltxKduqgVRUS5vP5BN8Y6ky0f_plhyy7uIVi6kIkWnQEwD8W4nic38ojFHZZ1Hvomzf75GMRLr_VlSHUsS76BAks0qsuEpSE4r7RlcMqEtgKmme03Hugj0q-2Lq_lca4mGnRU-UYlSHmUKq0FeUngsNaOyp5a48QV3DuHmgETk2jIc7IqEfSMWTPvrWYzv9_zJUH-0jKotqXt_l4SVJhRaJboSM')">
            </div>
            <h1 class="text-lg font-bold tracking-tight">CCL Elite</h1>
        </div>
        <div class="flex gap-4">
            <button class="relative p-2 rounded-full hover:bg-white/10 transition-colors">
                <span class="material-symbols-outlined text-primary">search</span>
            </button>
            <a href="{{ route('notice-board') }}" class="relative p-2 rounded-full hover:bg-white/10 transition-colors">
                <span class="material-symbols-outlined text-primary">notifications</span>
                <span class="absolute top-2 right-2 flex h-2 w-2 rounded-full bg-red-500 border border-brand-blue"></span>
            </a>
        </div>
    </header>

    {{-- Profile Hero --}}
    <section class="px-6 py-6 flex flex-col gap-6">
        <div class="flex items-center gap-5">
            <div class="relative">
                <div class="size-20 rounded-full gold-border p-1 bg-background-dark">
                    <div class="size-full rounded-full bg-center bg-cover"
                         style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuC3w-TJFde_9TEMHbamKnTw7N5hUUyUtNv_-uYT_d9TLP8bSnV6hpIDYOmslEUbYalA-8QP5WDATayvWh6aXvBuMudfPMD_VQyEUWGbODQWmmoSPvzPVBO3LCU_KX6MuKXasQhXgMRRY0UHvcr8L_Ftaof-eTVScAsNGaPQ1fh2V7N5b4TelYgAZ2I-hKuE_IVxdO_t5gpgEFevUxONp15fjSc2mHDzhyRaqLXuI9HZhN8ZfZXKVVhb7ldaA7V4p2r94TJ2BanfIQY')">
                    </div>
                </div>
                <div class="absolute -bottom-1 -right-1 bg-primary text-brand-blue px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider">PRO</div>
            </div>
            <div class="flex flex-col">
                <p class="text-white/60 text-sm font-medium">Welcome back,</p>
                <h2 class="text-2xl font-bold tracking-tight">Ahmed Bin Jaffer</h2>
                <p class="text-primary text-sm font-semibold mt-1">ID: CCL-8829-2024</p>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="bg-white/10 border border-white/10 rounded-xl p-5 backdrop-blur-sm flex justify-between items-center">
            <div class="space-y-1">
                <p class="text-xs uppercase tracking-widest text-white/50">Current Status</p>
                <p class="text-lg font-bold text-primary">Platinum Member</p>
            </div>
            <div class="h-10 w-px bg-white/10"></div>
            <div class="space-y-1 text-right">
                <p class="text-xs uppercase tracking-widest text-white/50">Ledger Balance</p>
                <p class="text-lg font-bold text-white">৳ 12,450.00</p>
            </div>
        </div>
    </section>

    {{-- Action Grid --}}
    <section class="px-6 pb-8">
        <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-white/40 mb-6">Club Services</h3>
        <div class="grid grid-cols-3 gap-y-8 gap-x-4">

            @php
            $services = [
                ['route' => 'notice-board', 'icon' => 'campaign',               'label' => 'Notice Board'],
                ['route' => 'ledger',       'icon' => 'account_balance_wallet', 'label' => 'Ledger',      'badge' => true],
                ['route' => 'facilities',   'icon' => 'event',                  'label' => 'Facilities'],
                ['route' => 'ledger',       'icon' => 'account_balance_wallet', 'label' => 'Ledger'],
                ['route' => 'directory',    'icon' => 'group',                  'label' => 'Directory'],
                ['route' => 'shop',         'icon' => 'shopping_bag',           'label' => 'Club Shop'],
                ['route' => 'executive',    'icon' => 'gavel',                  'label' => 'Committee'],
                ['route' => 'contact',      'icon' => 'call',                   'label' => 'Contact'],
                ['route' => 'profile',      'icon' => 'badge',                  'label' => 'My Profile'],
            ];
            @endphp

            @foreach ($services as $s)
            <div class="flex flex-col items-center gap-3">
                <a href="{{ route($s['route']) }}"
                   class="size-20 rounded-full bg-primary/10 border border-primary/20 flex items-center justify-center transition-all active:scale-90 relative">
                    <span class="material-symbols-outlined text-primary text-3xl">{{ $s['icon'] }}</span>
                    @if (!empty($s['badge']))
                    <div class="absolute top-4 right-4 size-3 bg-primary rounded-full border-2 border-brand-blue"></div>
                    @endif
                </a>
                <span class="text-xs font-semibold text-white/80 text-center tracking-tight">{{ $s['label'] }}</span>
            </div>
            @endforeach

        </div>

        {{-- Upcoming Event --}}
        <div class="mt-12">
            <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-white/40 mb-6">Upcoming Highlight</h3>
            <div class="bg-white/10 rounded-xl overflow-hidden border border-white/5">
                <div class="h-40 w-full bg-center bg-cover"
                     style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAnT7AHaLAQLOvqNQ227xQBwjRJIjM-qXBPDh0JSkhCGeeFwcnUeyeC_9rQBP1l8XTUFaVW7-aoU5SRpflqOn0-moqgF40JPYbXBm2Ipe3ruwmfduF8-CssyYPAHqx81RoRZQZnhtBChfNj4plnfKLJjJ8mZoQ9FYtohcSAxa41skc9_dcOlWYKbfjX9rTAKouok7TdZVNe8xdqhLtDtJARje4g26fdQATUOqnbOwYm5_NJj3NDhZInWfA3guJMAoIJmkmKsRq2Cao')">
                </div>
                <div class="p-5 space-y-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="text-lg font-bold">Annual Grand Gala Dinner</h4>
                            <p class="text-white/60 text-sm">Dec 24 • Grand Ballroom • 7:00 PM</p>
                        </div>
                        <div class="bg-primary text-brand-blue rounded-lg px-3 py-1 flex flex-col items-center leading-tight">
                            <span class="text-[10px] font-bold">DEC</span>
                            <span class="text-lg font-black">24</span>
                        </div>
                    </div>
                    <button class="w-full bg-primary py-3 rounded-full text-brand-blue font-bold text-sm tracking-wide transition-all active:scale-95">
                        RESERVE A TABLE
                    </button>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
