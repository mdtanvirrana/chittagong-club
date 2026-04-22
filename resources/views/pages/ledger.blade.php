@extends('layouts.userpanel')
@section('page_title', 'My Ledger')
@section('show_nav', true)

@php
    $paymentState = request()->query('payment');

    $paymentNotice = match ($paymentState) {
        'success' => ['tone' => 'success', 'message' => 'Payment completed successfully.'],
        'failed' => ['tone' => 'danger', 'message' => 'Payment failed. Please try again.'],
        'cancelled' => ['tone' => 'warning', 'message' => 'Payment was cancelled before completion.'],
        'verification_failed' => ['tone' => 'danger', 'message' => 'Payment callback received, but verification failed.'],
        'missing' => ['tone' => 'warning', 'message' => 'Payment callback received for an unknown transaction.'],
        default => null,
    };
@endphp

@section('userpanel_content')
    <div
        x-data="ledgerPage({
        dataUrl: @js(route('ledger.data')),
        monthDetailsUrl: @js(route('ledger.month-details')),
        initiatePaymentUrl: @js(route('ledger.payments.sslcommerz.initiate')),
        csrfToken: @js(csrf_token()),
        paymentNotice: @js($paymentNotice),
    })"
        x-init="init()"
        @keydown.escape.window="handleEscape()"
        class="flex min-h-screen flex-col pb-24"
    >
        <header class="userpanel-subheader sticky top-0 z-50 border-b border-white/10 bg-brand-blue/90 px-4 pb-4 pt-12 ios-blur">
            <div class="mb-4 flex items-center justify-between">
                <a href="{{ route('dashboard') }}"
                   class="flex size-10 items-center justify-center rounded-full transition-colors hover:bg-white/10">
                    <span class="material-symbols-outlined text-white">arrow_back_ios</span>
                </a>
                <div class="text-center">
                    <p class="text-[14px] font-bold uppercase tracking-[0.2em] text-primary">{{ $companyName }}</p>
                    <h1 class="text-lg font-bold text-white">My Ledger</h1>
                </div>
                <button
                    @click="openPaymentModal()"
                    class=""
                >
                </button>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <button
                    @click="activeTab = 'overview'"
                    :class="activeTab === 'overview' ? 'bg-primary text-brand-blue' : 'bg-white/10 text-white/60'"
                    class="rounded-full py-2 text-sm font-bold transition-all"
                >Overview</button>
                <button
                    @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'bg-primary text-brand-blue' : 'bg-white/10 text-white/60'"
                    class="rounded-full py-2 text-sm font-bold transition-all"
                >History</button>
                <button
                    @click="activeTab = 'payments'"
                    :class="activeTab === 'payments' ? 'bg-primary text-brand-blue' : 'bg-white/10 text-white/60'"
                    class="rounded-full py-2 text-sm font-bold transition-all"
                >Payments</button>
            </div>
        </header>

        <div x-show="paymentNotice" x-cloak class="px-4 pt-4">
            <div class="rounded-2xl border px-4 py-3"
                 :class="paymentNoticeClasses()">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-base" x-text="paymentNoticeIcon()"></span>
                    <p class="text-sm font-medium" x-text="paymentNotice.message"></p>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'overview'" class="space-y-4 px-4 pt-4">
            <div class="rounded-2xl border border-primary/20 bg-gradient-to-br from-primary/15 to-white/5 p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-primary/80">Quick Payment</p>
                        <h2 class="mt-1 font-extrabold text-white">Pay outstanding dues with Online</h2>

                    </div>
                    <div class="rounded-2xl bg-white/10 py-2 px-3">
                        <span class="material-symbols-outlined text-2xl text-primary">payments</span>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between rounded-2xl bg-white/5 px-4 py-3">
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-white/40">Current Due</p>
                        <p class="text-base font-extrabold " x-text="loading ? '--' : formatMoney(state.totalDue, 2)"></p>
                    </div>
                    <button
                        @click="openPaymentModal()"
                        class="rounded-full bg-primary px-4 py-2 text-sm font-extrabold text-brand-blue transition-transform active:scale-95"
                    >
                        Make Payment
                    </button>
                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/10 p-5">
                <div class="mb-1 flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-widest text-white/50">Credit Limit</p>
                    <template x-if="loading">
                        <span class="h-5 w-16 animate-pulse rounded-full bg-white/10"></span>
                    </template>
                    <template x-if="!loading">
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                          :class="usageBadgeClass()"
                          x-text="state.usagePercent + '% used'"></span>
                    </template>
                </div>

                <p class="mt-1 text-3xl font-extrabold text-primary"
                   x-text="loading ? 'Loading...' : formatMoney(state.creditLimit, 2)"></p>

                <div class="mt-4 h-2 overflow-hidden rounded-full bg-white/10">
                    <div class="h-2 rounded-full transition-all duration-500"
                         :class="usageBarClass()"
                         :style="'width: ' + (loading ? 20 : state.usagePercent) + '%'"></div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-3">
                    <template x-for="item in overviewStats()" :key="item.label">
                        <div class="rounded-xl bg-white/5 p-3 text-center">
                            <p class="mb-1 text-[10px] uppercase tracking-wider text-white/40" x-text="item.label"></p>
                            <template x-if="loading">
                                <div class="mx-auto h-5 w-16 animate-pulse rounded bg-white/10"></div>
                            </template>
                            <template x-if="!loading">
                                <p class="text-sm font-bold" :class="item.className" x-text="item.value"></p>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/10">
                <div class="flex items-center gap-3 border-b border-white/10 px-4 py-3">
                    <div class="rounded-lg bg-primary/10 p-2">
                        <span class="material-symbols-outlined text-lg text-primary">insights</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-white" x-text="loading ? 'Loading insights...' : 'Monthly Insights'"></p>
                        <p class="text-xs text-white/40">Department-wise activity</p>
                    </div>
                    <div class="ml-auto text-right">
                        <p class="text-[10px] uppercase tracking-wider text-white/40" x-text="loading ? 'Loading insights...' : state.currentMonthLabel ">Spend</p>
                        <p class="text-sm font-bold text-white" x-text="loading ? '--' : formatMaybeMoney(state.thisMonthDebit, 0)"></p>
                    </div>
                </div>

                <template x-if="loading">
                    <div class="space-y-3 px-4 py-4">
                        <div class="h-14 animate-pulse rounded-xl bg-white/5"></div>
                        <div class="h-14 animate-pulse rounded-xl bg-white/5"></div>
                        <div class="h-14 animate-pulse rounded-xl bg-white/5"></div>
                    </div>
                </template>

                <template x-if="!loading && state.deptBreakdown.length === 0">
                    <div class="flex flex-col items-center py-8">
                        <span class="material-symbols-outlined mb-2 text-3xl text-white/20">receipt_long</span>
                        <p class="text-sm text-white/30" x-text="state.thisMonthDebit === null ? 'No monthly billing data available' : 'No department activity this month'"></p>
                    </div>
                </template>

                <template x-if="!loading && state.deptBreakdown.length > 0">
                    <div>
                        <div class="divide-y divide-white/5">
                            <template x-for="dept in state.deptBreakdown" :key="dept.dept">
                                <div class="px-4 py-3">
                                    <div class="mb-1.5 flex items-center justify-between">
                                        <p class="text-sm font-medium text-white" x-text="dept.dept"></p>
                                        <div class="text-right">
                                            <p class="text-sm font-bold" :class="deptAmountClass(dept)" x-text="deptAmountLabel(dept)"></p>
                                            <p class="text-[10px] text-green-400"
                                               x-show="Number(dept.debit_amount ?? 0) > 0 && Number(dept.credit_amount ?? 0) > 0"
                                               x-text="'+' + formatMoney(dept.credit_amount, 0) + ' credited'"></p>
                                            <p class="text-[10px] text-white/30" x-text="dept.count + ' txn' + (dept.count > 1 ? 's' : '')"></p>
                                        </div>
                                    </div>
                                    <div class="h-1.5 overflow-hidden rounded-full bg-white/10">
                                        <div class="h-1.5 rounded-full"
                                             :class="deptBarClass(dept)"
                                             :style="'width: ' + deptPercent(dept) + '%'"></div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div x-show="state.thisMonthCredit > 0"
                             class="flex items-center justify-between border-t border-white/10 bg-green-500/5 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-base text-green-400">add_circle</span>
                                <p class="text-sm text-white/60">Credits received</p>
                            </div>
                            <p class="text-sm font-bold text-green-400" x-text="'+' + formatMoney(state.thisMonthCredit, 0)"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="activeTab === 'history'" class="space-y-3 px-4 pt-4">
            <div class="space-y-3 rounded-xl border border-white/10 bg-white/10 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-primary">date_range</span>
                        <p class="text-sm font-bold text-white">Filter by Period</p>
                    </div>
                    <button
                        @click="clearFilter()"
                        x-show="fromDate || toDate"
                        class="text-[10px] font-bold uppercase tracking-wider text-primary"
                    >Clear</button>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="mb-1.5 text-[10px] uppercase tracking-wider text-white/40">From</p>
                        <input type="month" autocomplete="off" x-model="fromDate"
                               class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white [color-scheme:dark] focus:border-primary/30 focus:outline-none focus:ring-2 focus:ring-primary/50" />
                    </div>
                    <div>
                        <p class="mb-1.5 text-[10px] uppercase tracking-wider text-white/40">To</p>
                        <input type="month" autocomplete="off" x-model="toDate"
                               class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white [color-scheme:dark] focus:border-primary/30 focus:outline-none focus:ring-2 focus:ring-primary/50" />
                    </div>
                </div>

                <div x-show="fromDate || toDate" class="flex items-center gap-2 pt-1">
                    <span class="material-symbols-outlined text-sm text-primary/60">info</span>
                    <p class="text-xs text-white/40">
                        Showing <span class="font-bold text-primary" x-text="filteredHistory.length"></span>
                        of <span x-text="state.monthlyHistory.length"></span> months
                    </p>
                </div>
            </div>

            <template x-if="loading">
                <div class="space-y-3">
                    <div class="h-20 animate-pulse rounded-xl bg-white/5"></div>
                    <div class="h-20 animate-pulse rounded-xl bg-white/5"></div>
                    <div class="h-20 animate-pulse rounded-xl bg-white/5"></div>
                </div>
            </template>

            <template x-if="!loading && filteredHistory.length === 0">
                <div class="flex flex-col items-center py-16">
                    <span class="material-symbols-outlined mb-3 text-5xl text-white/20">receipt_long</span>
                    <p class="text-sm text-white/40">No months match the selected period</p>
                    <button @click="clearFilter()" class="mt-3 text-sm font-bold text-primary">Clear filter</button>
                </div>
            </template>

            <template x-for="month in filteredHistory" :key="month.month_key">
                <button
                    @click="openMonthModal(month)"
                    class="flex w-full items-center gap-4 rounded-xl border border-white/10 bg-white/5 p-4 text-left transition-transform active:scale-[0.98]"
                >
                    <div class="flex size-12 shrink-0 flex-col items-center justify-center rounded-xl bg-primary/10">
                        <p class="text-xs font-extrabold leading-none text-primary" x-text="month.month_short"></p>
                        <p class="mt-0.5 text-[10px] leading-none text-white/40" x-text="month.month_year"></p>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-white" x-text="month.month_label"></p>
                        <p class="mt-0.5 text-xs text-white/40" x-text="month.row_count + ' transactions'"></p>

                    </div>

                    <div class="shrink-0 text-right">
                        <p class="text-sm font-bold "
                           x-text="formatMoney(month.total_debit, 0)"></p>
                        <p class="mt-0.5 text-xs text-white/50">spent</p>
                    </div>

                    <span class="material-symbols-outlined shrink-0 text-white/20">chevron_right</span>
                </button>
            </template>
        </div>

        <div x-show="activeTab === 'payments'" class="space-y-4 px-4 pt-4">
            <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-white">All Transactions</p>
                        <p class="text-xs text-white/40">Every payment attempt is stored here.</p>
                    </div>
                    <button
                        @click="openPaymentModal()"
                        class="rounded-full border border-primary/40 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-primary"
                    >New</button>
                </div>

                <div class="overflow-x-auto rounded-xl border border-white/10">
                    <table class="min-w-full text-left text-xs text-white/70">
                        <thead class="bg-white/5 text-[10px] uppercase tracking-widest text-white/40">
                        <tr>
                            <th class="px-3 py-3">Transaction</th>
                            <th class="px-3 py-3">Amount</th>
                            <th class="px-3 py-3">Note</th>
                            <th class="px-3 py-3">Status</th>
                            <th class="px-3 py-3">Date</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                        <template x-if="state.paymentTransactions.length === 0">
                            <tr>
                                <td colspan="5" class="px-3 py-8 text-center text-sm text-white/35">No payment transactions yet.</td>
                            </tr>
                        </template>
                        <template x-for="transaction in state.paymentTransactions" :key="transaction.transaction_id">
                            <tr class="align-top">
                                <td class="px-3 py-3">
                                    <p class="font-semibold text-white" x-text="transaction.transaction_id"></p>
                                    <p class="mt-1 text-[10px] text-white/35" x-text="transaction.card_type || transaction.ssl_status || 'Pending gateway data'"></p>
                                </td>
                                <td class="px-3 py-3 font-semibold text-white" x-text="formatMoney(transaction.amount, 2)"></td>
                                <td class="px-3 py-3 text-white/60" x-text="transaction.note || '?'"></td>
                                <td class="px-3 py-3">
                                    <span class="rounded-full px-2 py-1 text-[10px] font-bold uppercase tracking-wider"
                                          :class="paymentStatusClasses(transaction.status)"
                                          x-text="transaction.status"></span>
                                </td>
                                <td class="px-3 py-3 text-white/50" x-text="transaction.paid_at || transaction.updated_at || '?'"></td>
                            </tr>
                        </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                <div class="mb-3">
                    <p class="text-sm font-bold text-white">Successful Transactions</p>
                    <p class="text-xs text-white/40">Only validated SSLCommerz payments appear here.</p>
                </div>

                <div class="overflow-x-auto rounded-xl border border-white/10">
                    <table class="min-w-full text-left text-xs text-white/70">
                        <thead class="bg-white/5 text-[10px] uppercase tracking-widest text-white/40">
                        <tr>
                            <th class="px-3 py-3">Transaction</th>
                            <th class="px-3 py-3">Amount</th>
                            <th class="px-3 py-3">Note</th>
                            <th class="px-3 py-3">Gateway</th>
                            <th class="px-3 py-3">Paid At</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                        <template x-if="state.successfulTransactions.length === 0">
                            <tr>
                                <td colspan="5" class="px-3 py-8 text-center text-sm text-white/35">No successful transactions yet.</td>
                            </tr>
                        </template>
                        <template x-for="transaction in state.successfulTransactions" :key="transaction.transaction_id">
                            <tr class="align-top">
                                <td class="px-3 py-3">
                                    <p class="font-semibold text-white" x-text="transaction.transaction_id"></p>
                                    <p class="mt-1 text-[10px] text-green-400">Validated</p>
                                </td>
                                <td class="px-3 py-3 font-semibold text-white" x-text="formatMoney(transaction.amount, 2)"></td>
                                <td class="px-3 py-3 text-white/60" x-text="transaction.note || '?'"></td>
                                <td class="px-3 py-3 text-white/50" x-text="transaction.bank_transaction_id || transaction.card_type || '?'"></td>
                                <td class="px-3 py-3 text-white/50" x-text="transaction.paid_at || '?'"></td>
                            </tr>
                        </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <template x-if="monthModal !== null">
            <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/60" @click="closeMonthModal()"></div>

                <div class="relative w-full max-w-[425px] overflow-hidden rounded-3xl border border-white/10 bg-[#0a3d62]"
                     style="max-height: 85dvh;"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="scale-95 opacity-0"
                     x-transition:enter-end="scale-100 opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="scale-100 opacity-100"
                     x-transition:leave-end="scale-95 opacity-0">
                    <div class="flex justify-center pb-1 pt-3">
                        <div class="h-1 w-10 rounded-full bg-white/20"></div>
                    </div>

                    <div class="flex items-center justify-between border-b border-white/10 px-5 py-3">
                        <div>
                            <p class="text-base font-extrabold text-white" x-text="monthModal.month_label"></p>
                            <p class="text-xs text-white/40">Department-wise breakdown</p>
                        </div>
                        <button @click="closeMonthModal()"
                                class="flex size-8 items-center justify-center rounded-full bg-white/10 text-white/60">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>

                    <template x-if="monthModalLoading">
                        <div class="space-y-3 px-5 py-5">
                            <div class="h-16 animate-pulse rounded-xl bg-white/5"></div>
                            <div class="h-20 animate-pulse rounded-xl bg-white/5"></div>
                            <div class="h-20 animate-pulse rounded-xl bg-white/5"></div>
                        </div>
                    </template>

                    <template x-if="!monthModalLoading && monthModalData">
                        <div>
                            <div class="max-h-[calc(85dvh-180px)] overflow-y-auto divide-y divide-white/10 px-5">
                                <template x-for="dept in monthModalData.depts" :key="dept.dept">
                                    <div class="py-4">
                                        <div class="mb-2 flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="flex size-8 items-center justify-center rounded-lg bg-primary/10">
                                                    <span class="material-symbols-outlined text-base text-primary">storefront</span>
                                                </div>
                                                <p class="text-sm font-bold text-white" x-text="dept.dept"></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-bold text-white" x-show="dept.total_debit > 0" x-text="formatMoney(dept.total_debit, 0)"></p>
                                                <p class="text-xs text-green-400" x-show="dept.total_credit > 0" x-text="'+' + formatMoney(dept.total_credit, 0)"></p>
                                            </div>
                                        </div>

                                        <div class="space-y-1 pl-10">
                                            <template x-for="(entry, i) in dept.entries" :key="i">
                                                <div class="flex items-center justify-between">
                                                    <div class="min-w-0 flex-1 pr-3">
                                                        <div class="flex items-center gap-2">
                                                            <p class="truncate text-xs text-white/60" x-text="entry.InvMRN">

                                                            </p>
                                                        </div>
                                                        <p class="text-[10px] text-white/30" x-text="entry.Note"></p>
                                                    </div>
                                                    <div class="shrink-0 text-right">

                                                        <p class="text-xs font-medium text-white" x-show="entry.DrAmt > 0" x-text="formatMoney(entry.DrAmt, 0)"></p>
                                                        <p class="text-xs font-medium text-green-400" x-show="entry.CrAmt > 0" x-text="'+' + formatMoney(entry.CrAmt, 0)"></p>
                                                        <p class="text-[10px] text-white/30" x-text="entry.EDate"></p>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <template x-if="paymentModalOpen">
            <div class="fixed inset-0 z-[110] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/60" @click="closePaymentModal()"></div>

                <div class="relative w-full max-w-[425px] overflow-hidden rounded-3xl border border-white/10 bg-[#0a3d62] shadow-2xl"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="scale-95 opacity-0"
                     x-transition:enter-end="scale-100 opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="scale-100 opacity-100"
                     x-transition:leave-end="scale-95 opacity-0">
                    <div class="border-b border-white/10 px-5 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-base font-extrabold text-white">Online Payment</p>
                                <p class="text-xs text-white/40">Enter the payable amount and an optional note.</p>
                            </div>
                            <button @click="closePaymentModal()"
                                    class="flex size-8 items-center justify-center rounded-full bg-white/10 text-white/60">
                                <span class="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-4 px-5 py-5">
                        <div x-show="paymentFormError" class="rounded-2xl border border-red-400/30 bg-red-500/10 px-4 py-3 text-sm text-red-200" x-text="paymentFormError"></div>

                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-white/40">Amount</label>
                            <input
                                x-model="paymentForm.amount"
                                type="number"
                                min="10"
                                step="0.01"
                                placeholder="Enter amount"
                                class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-primary/30 focus:outline-none focus:ring-2 focus:ring-primary/50"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-white/40">Note</label>
                            <textarea
                                x-model="paymentForm.note"
                                rows="4"
                                placeholder="Payment note"
                                class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-primary/30 focus:outline-none focus:ring-2 focus:ring-primary/50"
                            ></textarea>
                        </div>

                        <button
                            @click="submitPayment()"
                            :disabled="paymentSubmitting"
                            class="flex w-full items-center justify-center gap-2 rounded-2xl bg-primary px-4 py-3 text-sm font-extrabold text-brand-blue transition disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span class="material-symbols-outlined text-base" x-show="!paymentSubmitting">lock</span>
                            <span x-text="paymentSubmitting ? 'Redirecting to SSLCommerz...' : 'Continue to Payment'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <script>
        function ledgerPage(config) {
            return {
                activeTab: 'overview',
                loading: true,
                fromDate: '',
                toDate: '',
                monthModal: null,
                monthModalData: null,
                monthModalLoading: false,
                paymentModalOpen: false,
                paymentSubmitting: false,
                paymentFormError: '',
                paymentNotice: config.paymentNotice,
                paymentForm: {
                    amount: '',
                    note: '',
                },
                state: {
                    creditLimit: null,
                    totalDue: null,
                    remaining: null,
                    thisMonthDebit: null,
                    thisMonthCredit: null,
                    usagePercent: 0,
                    currentMonthLabel: '',
                    deptBreakdown: [],
                    monthlyHistory: [],
                    paymentTransactions: [],
                    successfulTransactions: [],
                },

                get filteredHistory() {
                    const history = this.state.monthlyHistory.filter((month) => {
                        if (this.fromDate && month.month_key < this.fromDate) return false;
                        if (this.toDate && month.month_key > this.toDate) return false;
                        return true;
                    });

                    if (!this.fromDate && !this.toDate) {
                        return history.slice(0, 12);
                    }

                    return history;
                },

                init() {
                    this.fetchLedgerData();
                },

                handleEscape() {
                    if (this.paymentModalOpen) {
                        this.closePaymentModal();
                        return;
                    }

                    if (this.monthModal) {
                        this.closeMonthModal();
                    }
                },

                async fetchLedgerData() {
                    this.loading = true;

                    try {
                        const response = await fetch(config.dataUrl, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin',
                            cache: 'no-store',
                        });

                        if (!response.ok) {
                            throw new Error('Failed to load ledger data.');
                        }

                        this.state = await response.json();
                    } catch (error) {
                        console.error(error);
                    } finally {
                        this.loading = false;
                    }
                },

                async openMonthModal(month) {
                    this.monthModal = month;
                    this.monthModalData = null;
                    this.monthModalLoading = true;

                    try {
                        const url = new URL(config.monthDetailsUrl, window.location.origin);
                        url.searchParams.set('month', month.month_key);

                        const response = await fetch(url.toString(), {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin',
                            cache: 'no-store',
                        });

                        if (!response.ok) {
                            throw new Error('Failed to load month details.');
                        }

                        this.monthModalData = await response.json();
                    } catch (error) {
                        console.error(error);
                        this.monthModalData = { total_debit: 0, total_credit: 0, depts: [] };
                    } finally {
                        this.monthModalLoading = false;
                    }
                },

                closeMonthModal() {
                    this.monthModal = null;
                    this.monthModalData = null;
                    this.monthModalLoading = false;
                },

                openPaymentModal() {
                    this.paymentForm.amount = this.state.totalDue ? Number(this.state.totalDue).toFixed(2) : '';
                    this.paymentForm.note = '';
                    this.paymentFormError = '';
                    this.paymentModalOpen = true;
                },

                closePaymentModal() {
                    this.paymentModalOpen = false;
                    this.paymentSubmitting = false;
                    this.paymentFormError = '';
                },

                async submitPayment() {
                    const amount = Number(this.paymentForm.amount);

                    if (!Number.isFinite(amount) || amount < 10 || amount > 500000) {
                        this.paymentFormError = 'Enter a valid amount between 10 and 500000.';
                        return;
                    }

                    this.paymentSubmitting = true;
                    this.paymentFormError = '';

                    try {
                        const response = await fetch(config.initiatePaymentUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                amount: Number(amount).toFixed(2),
                                note: this.paymentForm.note,
                            }),
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'Unable to initiate payment.');
                        }

                        if (!data.gateway_url) {
                            throw new Error('SSLCommerz gateway URL is missing.');
                        }

                        window.location.href = data.gateway_url;
                    } catch (error) {
                        this.paymentSubmitting = false;
                        this.paymentFormError = error.message || 'Unable to initiate payment.';
                    }
                },

                clearFilter() {
                    this.fromDate = '';
                    this.toDate = '';
                },

                formatMoney(value, decimals = 0) {
                    const amount = Number(value ?? 0);
                    return  '' + amount.toLocaleString(undefined, {
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals,
                    });
                },

                formatMaybeMoney(value, decimals = 0) {
                    if (value === null || value === undefined || value === '') {
                        return '--';
                    }

                    return this.formatMoney(value, decimals);
                },

                overviewStats() {
                    return [
                        {
                            label: 'Total Due',
                            value: this.formatMoney(this.state.totalDue, 0),
                            className: '',
                        },
                        {
                            label: 'Remaining',
                            value: this.formatMoney(Math.abs(this.state.remaining ?? 0), 0),
                            className: '',
                        },
                        {
                            label: 'This Month',
                            value: this.formatMaybeMoney(this.state.thisMonthDebit, 0),
                            className: 'text-white',
                        },
                    ];
                },

                deptAmountLabel(dept) {
                    const debit = Number(dept?.debit_amount ?? 0);
                    const credit = Number(dept?.credit_amount ?? 0);

                    if (debit > 0) {
                        return this.formatMoney(debit, 0);
                    }

                    return '+' + this.formatMoney(credit, 0);
                },

                deptAmountClass(dept) {
                    return Number(dept?.debit_amount ?? 0) > 0 ? 'text-white' : 'text-green-400';
                },

                deptPercent(dept) {
                    const debit = Number(dept?.debit_amount ?? 0);
                    const credit = Number(dept?.credit_amount ?? 0);

                    if (debit > 0) {
                        const totalDebit = Number(this.state.thisMonthDebit ?? 0);
                        return totalDebit > 0 ? Math.round((debit / totalDebit) * 100) : 0;
                    }

                    const totalCredit = Number(this.state.thisMonthCredit ?? 0);
                    return totalCredit > 0 ? Math.round((credit / totalCredit) * 100) : 0;
                },

                deptBarClass(dept) {
                    return Number(dept?.debit_amount ?? 0) > 0 ? 'bg-primary/70' : 'bg-green-400/70';
                },

                usageBadgeClass() {
                    if ((this.state.usagePercent ?? 0) >= 90) return 'bg-red-500/20 text-red-400';
                    if ((this.state.usagePercent ?? 0) >= 70) return 'bg-amber-500/20 text-amber-400';
                    return 'bg-green-500/20 text-green-400';
                },

                usageBarClass() {
                    if ((this.state.usagePercent ?? 0) >= 90) return 'bg-red-400';
                    if ((this.state.usagePercent ?? 0) >= 70) return 'bg-amber-400';
                    return 'bg-primary';
                },

                paymentStatusClasses(status) {
                    switch (status) {
                        case 'success':
                            return 'bg-green-500/20 text-green-400';
                        case 'failed':
                        case 'init_failed':
                        case 'verification_failed':
                            return 'bg-red-500/20 text-red-300';
                        case 'cancelled':
                            return 'bg-amber-500/20 text-amber-300';
                        default:
                            return 'bg-white/10 text-white/70';
                    }
                },

                paymentNoticeClasses() {
                    switch (this.paymentNotice?.tone) {
                        case 'success':
                            return 'border-green-400/30 bg-green-500/10 text-green-100';
                        case 'danger':
                            return 'border-red-400/30 bg-red-500/10 text-red-100';
                        default:
                            return 'border-amber-400/30 bg-amber-500/10 text-amber-100';
                    }
                },

                paymentNoticeIcon() {
                    switch (this.paymentNotice?.tone) {
                        case 'success':
                            return 'check_circle';
                        case 'danger':
                            return 'error';
                        default:
                            return 'info';
                    }
                },
            };
        }
    </script>
@endsection
