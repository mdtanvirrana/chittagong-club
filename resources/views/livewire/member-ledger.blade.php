<div class="flex flex-col min-h-screen pb-24">

    {{-- ── Header ──────────────────────────────────────── --}}
    <header class="sticky top-0 z-50 bg-brand-blue/90 ios-blur border-b border-white/10 px-4 pt-12 pb-4">
        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('dashboard') }}"
               class="flex size-10 items-center justify-center rounded-full hover:bg-white/10 transition-colors">
                <span class="material-symbols-outlined text-white">arrow_back_ios</span>
            </a>
            <div class="text-center">
                <p class="text-primary text-[10px] uppercase tracking-[0.2em] font-bold">{{ $companyName }}</p>
                <h1 class="text-white text-lg font-bold">My Ledger</h1>
            </div>
            <div class="size-10"></div>
        </div>

        {{-- Tabs --}}
        <div class="flex gap-2">
            <button
                wire:click="$set('activeTab', 'overview')"
                class="flex-1 py-2 rounded-full text-sm font-bold transition-all
                       {{ $activeTab === 'overview' ? 'bg-primary text-brand-blue' : 'bg-white/10 text-white/60' }}"
            >Overview</button>
            <button
                wire:click="$set('activeTab', 'history')"
                class="flex-1 py-2 rounded-full text-sm font-bold transition-all
                       {{ $activeTab === 'history' ? 'bg-primary text-brand-blue' : 'bg-white/10 text-white/60' }}"
            >History</button>
        </div>
    </header>

    {{-- ════════════════════════════════════════════════════
         TAB 1 — OVERVIEW
    ════════════════════════════════════════════════════════ --}}
    @if ($activeTab === 'overview')
    <div class="px-4 pt-4 space-y-4">

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
                {{ number_format($creditLimit, 2) }}
            </p>

            {{-- Usage bar --}}
            <div class="mt-4 bg-white/10 rounded-full h-2 overflow-hidden">
                <div
                    class="h-2 rounded-full transition-all duration-500
                           {{ $usagePercent >= 90 ? 'bg-red-400' : ($usagePercent >= 70 ? 'bg-amber-400' : 'bg-primary') }}"
                    style="width: {{ $usagePercent }}%"
                ></div>
            </div>

            {{-- 3 stat chips --}}
            <div class="grid grid-cols-3 gap-3 mt-4">
                <div class="bg-white/5 rounded-xl p-3 text-center">
                    <p class="text-white/40 text-[10px] uppercase tracking-wider mb-1">Total Due</p>
                    <p class="text-red-400 text-sm font-bold">{{ number_format($totalDue, 0) }}</p>
                </div>
                <div class="bg-white/5 rounded-xl p-3 text-center">
                    <p class="text-white/40 text-[10px] uppercase tracking-wider mb-1">Remaining</p>
                    <p class="{{ $remaining >= 0 ? 'text-green-400' : 'text-red-400' }} text-sm font-bold">
                        {{ number_format(abs($remaining), 0) }}
                    </p>
                </div>
                <div class="bg-white/5 rounded-xl p-3 text-center">
                    <p class="text-white/40 text-[10px] uppercase tracking-wider mb-1">This Month</p>
                    <p class="text-white text-sm font-bold">{{ number_format($thisMonthDebit, 0) }}</p>
                </div>
            </div>
        </div>

        {{-- Running month insights --}}
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
                    <p class="text-white font-bold text-sm">{{ number_format($thisMonthDebit, 0) }}</p>
                </div>
            </div>

            @if (count($deptBreakdown)==0)
            <div class="flex flex-col items-center py-8">
                <span class="material-symbols-outlined text-3xl text-white/20 mb-2">receipt_long</span>
                <p class="text-white/30 text-sm">No spending this month</p>
            </div>
            @else
            <div class="divide-y divide-white/5">
                @foreach ($deptBreakdown as $dept)
                @php
                    $pct = $thisMonthDebit > 0 ? round(($dept['amount'] / $thisMonthDebit) * 100) : 0;
                @endphp
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-white text-sm font-medium">{{ $dept['dept'] }}</p>
                        <div class="text-right">
                            <p class="text-white text-sm font-bold">{{ number_format($dept['amount'], 0) }}</p>
                            <p class="text-white/30 text-[10px]">{{ $dept['count'] }} transaction{{ $dept['count'] > 1 ? 's' : '' }}</p>
                        </div>
                    </div>
                    <div class="bg-white/10 rounded-full h-1.5 overflow-hidden">
                        <div class="h-1.5 bg-primary/70 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Month credit received --}}
            @if ($thisMonthCredit > 0)
            <div class="flex items-center justify-between px-4 py-3 border-t border-white/10 bg-green-500/5">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-green-400 text-base">add_circle</span>
                    <p class="text-white/60 text-sm">Credits received</p>
                </div>
                <p class="text-green-400 font-bold text-sm">+{{ number_format($thisMonthCredit, 0) }}</p>
            </div>
            @endif
            @endif
        </div>

    </div>
    @endif

    {{-- ════════════════════════════════════════════════════
         TAB 2 — HISTORY
    ════════════════════════════════════════════════════════ --}}
    @if ($activeTab === 'history')
    <div class="px-4 pt-4 space-y-3">

        @if (count($monthlyHistory)==0)
        <div class="flex flex-col items-center py-16">
            <span class="material-symbols-outlined text-5xl text-white/20 mb-3">receipt_long</span>
            <p class="text-white/40 text-sm">No transaction history</p>
        </div>
        @else
        @foreach ($monthlyHistory as $month)
        @php
            $net = $month['net'];
        @endphp
        <button
            wire:click="openModal('{{ $month['month_key'] }}')"
            class="w-full flex items-center gap-4 bg-white/5 border border-white/10 rounded-xl p-4 active:scale-[0.98] transition-transform text-left"
        >
            {{-- Month icon --}}
            <div class="shrink-0 size-12 rounded-xl bg-primary/10 flex flex-col items-center justify-center">
                <p class="text-primary font-extrabold text-xs leading-none">
                    {{ \Carbon\Carbon::createFromFormat('Y-m', $month['month_key'])->format('M') }}
                </p>
                <p class="text-white/40 text-[10px] leading-none mt-0.5">
                    {{ \Carbon\Carbon::createFromFormat('Y-m', $month['month_key'])->format('Y') }}
                </p>
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <p class="text-white font-bold text-sm">{{ $month['month_label'] }}</p>
                <p class="text-white/40 text-xs mt-0.5">{{ $month['row_count'] }} transactions</p>
                @if ($month['total_credit'] > 0)
                <p class="text-green-400 text-xs mt-0.5">+{{ number_format($month['total_credit'], 0) }} credited</p>
                @endif
            </div>

            {{-- Amount + chevron --}}
            <div class="text-right shrink-0">
                <p class="{{ $net > 0 ? 'text-red-400' : 'text-green-400' }} font-bold text-sm">
                    {{ $net > 0 ? '-' : '+' }}{{ number_format(abs($net), 0) }}
                </p>
                <p class="text-white/20 text-xs mt-0.5">{{ number_format($month['total_debit'], 0) }} spent</p>
            </div>

            <span class="material-symbols-outlined text-white/20 shrink-0">chevron_right</span>
        </button>
        @endforeach
        @endif

    </div>
    @endif

    {{-- ════════════════════════════════════════════════════
         MODAL — Month detail (dept-wise)
    ════════════════════════════════════════════════════════ --}}
    @if ($showModal)
    <div
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        x-data
        @keydown.escape.window="$wire.closeModal()"
    >
        {{-- Backdrop --}}
        <div
            class="absolute inset-0 bg-black/60 ios-blur"
            wire:click="closeModal"
        ></div>

        {{-- Sheet --}}
        <div
            class="relative w-full max-w-[425px] bg-[#0a3d62] rounded-3xl border border-white/10 overflow-hidden"
            style="max-height: 85dvh;"
            x-data
            x-init=\"$el.style.opacity='0'; $el.style.transform='scale(0.95)'; setTimeout(() => { $el.style.transition='opacity 0.3s ease, transform 0.3s ease'; $el.style.opacity='1'; $el.style.transform='scale(1)'; }, 10)\"
        >
            {{-- Handle --}}
            <div class="flex justify-center pt-3 pb-1">
                <div class="w-10 h-1 bg-white/20 rounded-full"></div>
            </div>

            {{-- Modal header --}}
            <div class="flex items-center justify-between px-5 py-3 border-b border-white/10">
                <div>
                    <p class="text-white font-extrabold text-base">
                        {{ $modalMonth ? \Carbon\Carbon::createFromFormat('Y-m', $modalMonth)->format('F Y') : '' }}
                    </p>
                    <p class="text-white/40 text-xs">Department-wise breakdown</p>
                </div>
                <button wire:click="closeModal"
                        class="flex size-8 items-center justify-center rounded-full bg-white/10 text-white/60">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            {{-- Modal body --}}
            <div class="overflow-y-auto" style="max-height: calc(85dvh - 120px);">

                @if (count($modalData) === 0)
                <div class="flex flex-col items-center py-10">
                    <p class="text-white/30 text-sm">No data for this month</p>
                </div>
                @else

                {{-- Summary row --}}
                @php
                    $modalTotalDebit  = $modalData->sum('total_debit');
                    $modalTotalCredit = $modalData->sum('total_credit');
                @endphp
                <div class="grid grid-cols-2 gap-3 px-5 py-4 border-b border-white/10">
                    <div class="bg-red-500/10 rounded-xl p-3 text-center">
                        <p class="text-white/40 text-[10px] uppercase tracking-wider mb-1">Total Spent</p>
                        <p class="text-red-400 font-bold text-base">{{ number_format($modalTotalDebit, 0) }}</p>
                    </div>
                    <div class="bg-green-500/10 rounded-xl p-3 text-center">
                        <p class="text-white/40 text-[10px] uppercase tracking-wider mb-1">Total Credit</p>
                        <p class="text-green-400 font-bold text-base">{{ number_format($modalTotalCredit, 0) }}</p>
                    </div>
                </div>

                {{-- Dept rows --}}
                <div class="divide-y divide-white/10 px-5">
                    @foreach ($modalData as $dept)
                    <div class="py-4">

                        {{-- Dept header --}}
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="size-8 rounded-lg bg-primary/10 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-primary text-base">storefront</span>
                                </div>
                                <p class="text-white font-bold text-sm">{{ $dept['dept'] }}</p>
                            </div>
                            <div class="text-right">
                                @if ($dept['total_debit'] > 0)
                                <p class="text-white font-bold text-sm">{{ number_format($dept['total_debit'], 0) }}</p>
                                @endif
                                @if ($dept['total_credit'] > 0)
                                <p class="text-green-400 text-xs">+{{ number_format($dept['total_credit'], 0) }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Individual entries --}}
                        <div class="space-y-1 pl-10">
                            @foreach ($dept['entries'] as $entry)
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0 pr-3">
                                    <p class="text-white/60 text-xs truncate">
                                        {{ $entry->Remarks ?: ($entry->Note ?: $entry->InvMRN ?: '—') }}
                                    </p>
                                    <p class="text-white/30 text-[10px]">
                                        {{ \Carbon\Carbon::parse($entry->EDate)->format('d M') }}
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    @if ($entry->DrAmt > 0)
                                    <p class="text-white text-xs font-medium">{{ number_format($entry->DrAmt, 0) }}</p>
                                    @endif
                                    @if ($entry->CrAmt > 0)
                                    <p class="text-green-400 text-xs font-medium">+{{ number_format($entry->CrAmt, 0) }}</p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                    </div>
                    @endforeach
                </div>
                @endif

            </div>
        </div>
    </div>
    @endif

    @include('layouts.bottom-nav')
</div>
