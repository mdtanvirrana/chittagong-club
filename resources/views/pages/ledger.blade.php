@extends('layouts.app')
@section('title', 'Ledger — Chittagong Club Ltd.')
@section('show_nav', true)

@section('content')
<div
    x-data="{
        activeTab: 'all',
        tabs: [
            { key:'all',      label:'All'      },
            { key:'credit',   label:'Credits'  },
            { key:'debit',    label:'Debits'   },
            { key:'pending',  label:'Pending'  },
        ],
        transactions: [
            { id:1, type:'debit',   category:'Dining',      label:'Imperial Hall Dinner',    amount:'-৳ 4,500',  date:'Dec 10',  icon:'restaurant',              status:'completed' },
            { id:2, type:'credit',  category:'Deposit',     label:'Balance Top-Up',          amount:'+৳ 20,000', date:'Dec 8',   icon:'account_balance_wallet',  status:'completed' },
            { id:3, type:'debit',   category:'Sports',      label:'Tennis Court Booking',    amount:'-৳ 1,200',  date:'Dec 6',   icon:'sports_tennis',           status:'completed' },
            { id:4, type:'debit',   category:'Shop',        label:'Gold Crest Polo x2',      amount:'-৳ 6,400',  date:'Dec 5',   icon:'shopping_bag',            status:'completed' },
            { id:5, type:'pending', category:'Subscription','label':'Q4 Membership Fee',     amount:'-৳ 3,500',  date:'Due Dec 31', icon:'card_membership',      status:'pending'   },
            { id:6, type:'debit',   category:'Wellness',    label:'Fitness Centre Monthly',  amount:'-৳ 2,000',  date:'Dec 1',   icon:'fitness_center',          status:'completed' },
            { id:7, type:'credit',  category:'Refund',      label:'Event Cancellation Refund',amount:'+৳ 5,000', date:'Nov 28',  icon:'undo',                    status:'completed' },
        ],
        get filtered() {
            if (this.activeTab === 'all') return this.transactions;
            return this.transactions.filter(t => t.type === this.activeTab);
        }
    }"
    class="flex flex-col min-h-screen pb-24"
>
    {{-- Header --}}
    <header class="sticky top-0 z-50 bg-brand-blue/90 ios-blur border-b border-white/10 px-4 pt-12 pb-4">
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('dashboard') }}" class="flex size-10 items-center justify-center rounded-full hover:bg-white/10 transition-colors">
                <span class="material-symbols-outlined text-white">arrow_back_ios</span>
            </a>
            <div class="text-center">
                <p class="text-primary text-[10px] uppercase tracking-[0.2em] font-bold">Chittagong Club Ltd</p>
                <h1 class="text-white text-lg font-bold">My Ledger</h1>
            </div>
            <button class="flex size-10 items-center justify-center rounded-full hover:bg-white/10 transition-colors">
                <span class="material-symbols-outlined text-primary">filter_list</span>
            </button>
        </div>

        {{-- Balance Card --}}
        <div class="bg-white/10 border border-white/10 rounded-xl p-5 flex justify-between items-center">
            <div>
                <p class="text-white/50 text-xs uppercase tracking-widest">Available Balance</p>
                <p class="text-3xl font-extrabold text-primary mt-1">৳ 12,450</p>
                <p class="text-white/40 text-xs mt-1">Updated Dec 10, 2024</p>
            </div>
            <button class="gold-gradient text-brand-blue font-bold text-xs px-4 py-2 rounded-full active:scale-95 transition-transform">
                Top Up
            </button>
        </div>
    </header>

    {{-- Tabs --}}
    <div class="px-4 pt-4 flex gap-2 overflow-x-auto hide-scrollbar">
        <template x-for="tab in tabs" :key="tab.key">
            <button
                @click="activeTab = tab.key"
                :class="activeTab === tab.key
                    ? 'bg-primary text-brand-blue font-bold'
                    : 'bg-white/10 text-white/60 border border-white/10'"
                class="flex h-9 shrink-0 items-center justify-center rounded-full px-5 text-sm transition-all"
                x-text="tab.label"
            ></button>
        </template>
    </div>

    {{-- Transactions --}}
    <main class="flex-1 p-4 space-y-3 mt-2">
        <template x-for="tx in filtered" :key="tx.id">
            <div class="flex items-center gap-4 bg-white/5 border border-white/10 rounded-xl p-4">
                <div
                    :class="tx.type === 'credit' ? 'bg-green-500/20 text-green-400' : tx.type === 'pending' ? 'bg-amber-500/20 text-amber-400' : 'bg-primary/10 text-primary'"
                    class="shrink-0 size-12 rounded-xl flex items-center justify-center"
                >
                    <span class="material-symbols-outlined text-xl" x-text="tx.icon"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white font-semibold text-sm truncate" x-text="tx.label"></p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-white/40 text-xs" x-text="tx.category"></span>
                        <span class="text-white/20 text-xs">•</span>
                        <span class="text-white/40 text-xs" x-text="tx.date"></span>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <p
                        :class="tx.type === 'credit' ? 'text-green-400' : tx.type === 'pending' ? 'text-amber-400' : 'text-white'"
                        class="font-bold text-sm"
                        x-text="tx.amount"
                    ></p>
                    <span
                        x-show="tx.status === 'pending'"
                        class="text-[10px] bg-amber-500/20 text-amber-400 rounded-full px-2 py-0.5 font-bold uppercase"
                    >Pending</span>
                </div>
            </div>
        </template>

        {{-- Empty state --}}
        <div x-show="filtered.length === 0" class="flex flex-col items-center py-16">
            <span class="material-symbols-outlined text-5xl text-white/20 mb-3">receipt_long</span>
            <p class="text-white/40">No transactions found</p>
        </div>
    </main>
</div>
@endsection
