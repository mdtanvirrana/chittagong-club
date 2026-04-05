@extends('layouts.app')
@section('title', 'My Ledger — Chittagong Club Ltd.')
@section('show_nav', true)

@section('content')
<div
    x-data="{
        activeTab: 'overview',
        modal: null,
        history: {{ json_encode($monthlyHistory) }},
        fromDate: '',
        toDate: '',

        get filteredHistory() {
            return this.history.filter(m => {
                if (this.fromDate && m.month_key < this.fromDate) return false;
                if (this.toDate   && m.month_key > this.toDate)   return false;
                return true;
            });
        },
        clearFilter() {
            this.fromDate = '';
            this.toDate   = '';
        },
        openModal(month) {
            this.modal = month;
        },
        closeModal() {
            this.modal = null;
        }
    }"
    @keydown.escape.window="closeModal()"
    class="flex flex-col min-h-screen pb-24"
>

    {{-- ── Header ──────────────────────────────────────── --}}
    <header class="sticky top-0 z-50 bg-brand-blue/90 ios-blur border-b border-white/10 px-4 pt-12 pb-4">
        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('dashboard') }}"
               class="flex size-10 items-center justify-center rounded-full hover:bg-white/10 transition-colors">
                <span class="material-symbols-outlined text-white">arrow_back_ios</span>
            </a>
            <div class="text-center">
                <p class="text-primary text-[10px] uppercase tracking-[0.2em] font-bold">Chittagong Club Ltd</p>
                <h1 class="text-white text-lg font-bold">My Ledger</h1>
            </div>
            <div class="size-10"></div>
        </div>

        {{-- Tabs --}}
        <div class="flex gap-2">
            <button
                @click="activeTab = 'overview'"
                :class="activeTab === 'overview' ? 'bg-primary text-brand-blue' : 'bg-white/10 text-white/60'"
                class="flex-1 py-2 rounded-full text-sm font-bold transition-all"
            >Overview</button>
            <button
                @click="activeTab = 'history'"
                :class="activeTab === 'history' ? 'bg-primary text-brand-blue' : 'bg-white/10 text-white/60'"
                class="flex-1 py-2 rounded-full text-sm font-bold transition-all"
            >History</button>
        </div>
    </header>


    {{-- ════════════════════════════════════════
         OVERVIEW TAB
    ════════════════════════════════════════ --}}
    <div x-show="activeTab === 'overview'" class="px-4 pt-4 space-y-4">

        {{-- Credit limit card --}}
        <div class="bg-white/10 border border-white/10 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-1">
                <p class="text-white/50 text-xs uppercase tracking-widest font-bold">Credit Limit</p>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full
                    {{ $usagePercent >= 90 ? 'bg-red-500/20 text-red-400' :
                       ($usagePercent >= 70 ? 'bg-amber-500/20 text-amber-400' : 'bg-green-500/20 text-green-400') }}">
                    {{ $usagePercent }}% used
                </span>
            </div>
            <p class="text-3xl font-extrabold text-primary mt-1">
                ৳{{ number_format($creditLimit, 2) }}
            </p>

            {{-- Usage bar --}}
            <div class="mt-4 bg-white/10 rounded-full h-2 overflow-hidden">
                <div class="h-2 rounded-full transition-all duration-500
                            {{ $usagePercent >= 90 ? 'bg-red-400' : ($usagePercent >= 70 ? 'bg-amber-400' : 'bg-primary') }}"
                     style="width: {{ $usagePercent }}%">
                </div>
            </div>

            {{-- 3 stat chips --}}
            <div class="grid grid-cols-3 gap-3 mt-4">
                <div class="bg-white/5 rounded-xl p-3 text-center">
                    <p class="text-white/40 text-[10px] uppercase tracking-wider mb-1">Total Due</p>
                    <p class="text-red-400 text-sm font-bold">৳{{ number_format($totalDue, 0) }}</p>
                </div>
                <div class="bg-white/5 rounded-xl p-3 text-center">
                    <p class="text-white/40 text-[10px] uppercase tracking-wider mb-1">Remaining</p>
                    <p class="{{ $remaining >= 0 ? 'text-green-400' : 'text-red-400' }} text-sm font-bold">
                        ৳{{ number_format(abs($remaining), 0) }}
                    </p>
                </div>
                <div class="bg-white/5 rounded-xl p-3 text-center">
                    <p class="text-white/40 text-[10px] uppercase tracking-wider mb-1">This Month</p>
                    <p class="text-white text-sm font-bold">৳{{ number_format($thisMonthDebit, 0) }}</p>
                </div>
            </div>
        </div>

        {{-- Current month insights --}}
        <div class="bg-white/10 border border-white/10 rounded-2xl overflow-hidden">
            <div class="flex items-center gap-3 px-4 py-3 border-b border-white/10">
                <div class="p-2 bg-primary/10 rounded-lg">
                    <span class="material-symbols-outlined text-primary text-lg">insights</span>
                </div>
                <div>
                    <p class="text-white font-bold text-sm">{{ $currentMonthLabel }} Insights</p>
                    <p class="text-white/40 text-xs">Spending by department</p>
                </div>
                <div class="ml-auto text-right">
                    <p class="text-white/40 text-[10px] uppercase tracking-wider">Spend</p>
                    <p class="text-white font-bold text-sm">৳{{ number_format($thisMonthDebit, 0) }}</p>
                </div>
            </div>

            @if ($deptBreakdown->isEmpty())
            <div class="flex flex-col items-center py-8">
                <span class="material-symbols-outlined text-3xl text-white/20 mb-2">receipt_long</span>
                <p class="text-white/30 text-sm">No spending this month</p>
            </div>
            @else
            <div class="divide-y divide-white/5">
                @foreach ($deptBreakdown as $dept)
                @php $pct = $thisMonthDebit > 0 ? round(($dept['amount'] / $thisMonthDebit) * 100) : 0; @endphp
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-white text-sm font-medium">{{ $dept['dept'] }}</p>
                        <div class="text-right">
                            <p class="text-white text-sm font-bold">৳{{ number_format($dept['amount'], 0) }}</p>
                            <p class="text-white/30 text-[10px]">{{ $dept['count'] }} txn{{ $dept['count'] > 1 ? 's' : '' }}</p>
                        </div>
                    </div>
                    <div class="bg-white/10 rounded-full h-1.5 overflow-hidden">
                        <div class="h-1.5 bg-primary/70 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>

            @if ($thisMonthCredit > 0)
            <div class="flex items-center justify-between px-4 py-3 border-t border-white/10 bg-green-500/5">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-green-400 text-base">add_circle</span>
                    <p class="text-white/60 text-sm">Credits received</p>
                </div>
                <p class="text-green-400 font-bold text-sm">+৳{{ number_format($thisMonthCredit, 0) }}</p>
            </div>
            @endif
            @endif
        </div>
    </div>


    {{-- ════════════════════════════════════════
         HISTORY TAB
    ════════════════════════════════════════ --}}
    <div x-show="activeTab === 'history'" class="px-4 pt-4 space-y-3">

        {{-- ── Date filter ───────────────────────────────── --}}
        <div class="bg-white/10 border border-white/10 rounded-xl p-4 space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-base">date_range</span>
                    <p class="text-white text-sm font-bold">Filter by Period</p>
                </div>
                <button
                    @click="clearFilter()"
                    x-show="fromDate || toDate"
                    class="text-[10px] text-primary font-bold uppercase tracking-wider"
                >Clear</button>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <p class="text-white/40 text-[10px] uppercase tracking-wider mb-1.5">From</p>
                    <input
                        type="month"
                        autocomplete="off"
                        x-model="fromDate"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary/30 [color-scheme:dark]"
                    />
                </div>
                <div>
                    <p class="text-white/40 text-[10px] uppercase tracking-wider mb-1.5">To</p>
                    <input
                        type="month"
                        autocomplete="off"
                        x-model="toDate"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary/30 [color-scheme:dark]"
                    />
                </div>
            </div>

            {{-- Active filter summary --}}
            <div x-show="fromDate || toDate" class="flex items-center gap-2 pt-1">
                <span class="material-symbols-outlined text-primary/60 text-sm">info</span>
                <p class="text-white/40 text-xs">
                    Showing
                    <span class="text-primary font-bold" x-text="filteredHistory.length"></span>
                    of {{ count($monthlyHistory) }} months
                </p>
            </div>
        </div>

        {{-- ── Month cards ────────────────────────────────── --}}
        <template x-for="month in filteredHistory" :key="month.month_key">
            <button
                @click="openModal(month)"
                class="w-full flex items-center gap-4 bg-white/5 border border-white/10 rounded-xl p-4 active:scale-[0.98] transition-transform text-left"
            >
                <div class="shrink-0 size-12 rounded-xl bg-primary/10 flex flex-col items-center justify-center">
                    <p class="text-primary font-extrabold text-xs leading-none" x-text="month.month_short"></p>
                    <p class="text-white/40 text-[10px] leading-none mt-0.5" x-text="month.month_year"></p>
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-white font-bold text-sm" x-text="month.month_label"></p>
                    <p class="text-white/40 text-xs mt-0.5" x-text="month.row_count + ' transactions'"></p>
                    <p class="text-green-400 text-xs mt-0.5"
                       x-show="month.total_credit > 0"
                       x-text="'+৳' + month.total_credit.toLocaleString() + ' credited'"></p>
                </div>

                <div class="text-right shrink-0">
                    <p class="font-bold text-sm"
                       :class="month.net > 0 ? 'text-red-400' : 'text-green-400'"
                       x-text="'৳' + Math.abs(month.net).toLocaleString()"></p>
                    <p class="text-white/20 text-xs mt-0.5"
                       x-text="'৳' + month.total_debit.toLocaleString() + ' spent'"></p>
                </div>

                <span class="material-symbols-outlined text-white/20 shrink-0">chevron_right</span>
            </button>
        </template>

        {{-- Empty state --}}
        <div x-show="filteredHistory.length === 0" class="flex flex-col items-center py-16">
            <span class="material-symbols-outlined text-5xl text-white/20 mb-3">receipt_long</span>
            <p class="text-white/40 text-sm">No months match the selected period</p>
            <button @click="clearFilter()" class="mt-3 text-primary text-sm font-bold">Clear filter</button>
        </div>

    </div>


    {{-- ════════════════════════════════════════
         MODAL — pure Alpine, zero server round-trip
    ════════════════════════════════════════ --}}
    <template x-if="modal !== null">
        <div class="fixed inset-0 z-[100] flex items-end justify-center">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60" @click="closeModal()"></div>

            {{-- Sheet --}}
            <div
                class="relative w-full max-w-[425px] bg-[#0a3d62] rounded-t-3xl border-t border-white/10 overflow-hidden"
                style="max-height: 85dvh;"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="transform translate-y-full"
                x-transition:enter-end="transform translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="transform translate-y-0"
                x-transition:leave-end="transform translate-y-full"
            >
                {{-- Handle --}}
                <div class="flex justify-center pt-3 pb-1">
                    <div class="w-10 h-1 bg-white/20 rounded-full"></div>
                </div>

                {{-- Modal header --}}
                <div class="flex items-center justify-between px-5 py-3 border-b border-white/10">
                    <div>
                        <p class="text-white font-extrabold text-base" x-text="modal.month_label"></p>
                        <p class="text-white/40 text-xs">Department-wise breakdown</p>
                    </div>
                    <button @click="closeModal()"
                            class="flex size-8 items-center justify-center rounded-full bg-white/10 text-white/60">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                {{-- Summary chips --}}
                <div class="grid grid-cols-2 gap-3 px-5 py-4 border-b border-white/10">
                    <div class="bg-red-500/10 rounded-xl p-3 text-center">
                        <p class="text-white/40 text-[10px] uppercase tracking-wider mb-1">Total Spent</p>
                        <p class="text-red-400 font-bold text-base"
                           x-text="'৳' + modal.total_debit.toLocaleString()"></p>
                    </div>
                    <div class="bg-green-500/10 rounded-xl p-3 text-center">
                        <p class="text-white/40 text-[10px] uppercase tracking-wider mb-1">Total Credit</p>
                        <p class="text-green-400 font-bold text-base"
                           x-text="'৳' + modal.total_credit.toLocaleString()"></p>
                    </div>
                </div>

                {{-- Dept list --}}
                <div class="overflow-y-auto divide-y divide-white/10 px-5"
                     style="max-height: calc(85dvh - 180px);">

                    <template x-for="dept in modal.depts" :key="dept.dept">
                        <div class="py-4">

                            {{-- Dept header --}}
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-8 rounded-lg bg-primary/10 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-primary text-base">storefront</span>
                                    </div>
                                    <p class="text-white font-bold text-sm" x-text="dept.dept"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-white font-bold text-sm"
                                       x-show="dept.total_debit > 0"
                                       x-text="'৳' + dept.total_debit.toLocaleString()"></p>
                                    <p class="text-green-400 text-xs"
                                       x-show="dept.total_credit > 0"
                                       x-text="'+৳' + dept.total_credit.toLocaleString()"></p>
                                </div>
                            </div>

                            {{-- Entries --}}
                            <div class="space-y-1 pl-10">
                                <template x-for="(entry, i) in dept.entries" :key="i">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0 pr-3">
                                            <p class="text-white/60 text-xs truncate" x-text="entry.Remarks"></p>
                                            <p class="text-white/30 text-[10px]" x-text="entry.EDate"></p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="text-white text-xs font-medium"
                                               x-show="entry.DrAmt > 0"
                                               x-text="'৳' + entry.DrAmt.toLocaleString()"></p>
                                            <p class="text-green-400 text-xs font-medium"
                                               x-show="entry.CrAmt > 0"
                                               x-text="'+৳' + entry.CrAmt.toLocaleString()"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>

                        </div>
                    </template>

                </div>
            </div>
        </div>
    </template>

</div>
@endsection