@extends('layouts.admin')

@section('page_title', 'Admin Accounts')
@section('page_eyebrow', 'Access Control')

@section('content')
<div class="space-y-4">
    <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[10px] uppercase tracking-[0.24em] text-white/35">Account Manager</p>
                <h2 class="mt-1 font-display text-xl font-bold text-white">Manage Admin Access</h2>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <form method="GET" action="{{ route('admin.admin-users.index') }}" class="flex min-w-0 items-center gap-2 border border-[#30384a] bg-slate-950/20 px-3 py-2 sm:min-w-[18rem]">
                    <span class="material-symbols-outlined text-[18px] text-white/35">search</span>
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Search user ID"
                        class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-white placeholder:text-white/25 focus:ring-0"
                    >
                </form>

                <div class="flex gap-2">
                    @if ($search !== '')
                        <a href="{{ route('admin.admin-users.index') }}" class="inline-flex h-10 items-center justify-center border border-[#30384a] px-4 text-sm text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                            Clear
                        </a>
                    @endif

                    <a href="{{ route('admin.admin-users.create') }}" class="inline-flex h-10 items-center justify-center border border-[#30384a] bg-admin-gold px-4 text-sm font-medium text-admin-ink">
                        Add Admin
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-admin-line/10 bg-white/[0.03] shadow-panel">
        <div class="flex items-center justify-between border-b border-admin-line/10 px-4 py-3">
            <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">Admin List</p>
            <p class="text-xs text-white/45">{{ $admins->total() }} records</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-admin-line/10 bg-slate-950/20 text-[10px] uppercase tracking-[0.2em] text-white/35">
                    <tr>
                        <th class="px-4 py-3 font-medium">User ID</th>
                        <th class="px-4 py-3 font-medium">Admin Access</th>
                        <th class="px-4 py-3 font-medium">Last Updated</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-admin-line/10">
                    @forelse ($admins as $admin)
                        <tr class="align-top">
                            <td class="px-4 py-3.5">
                                <p class="font-semibold text-white">{{ $admin->userid }}</p>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-admin-line/30 bg-admin-soft/70 px-2.5 py-1 text-xs font-semibold text-admin-gold">
                                    <span class="material-symbols-outlined text-[16px]">verified_user</span>
                                    Enabled
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-white/65">
                                {{ $admin->LastUpdateDate ? \Carbon\Carbon::parse($admin->LastUpdateDate)->format('M d, Y') : 'N/A' }}
                                <span class="text-white/35">{{ $admin->LastUpdateTime ?: '' }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('admin.admin-users.edit', $admin->userid) }}" class="inline-flex h-8 items-center border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.admin-users.destroy', $admin->userid) }}" onsubmit="return confirm('Delete this admin account?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex h-8 items-center border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-sm text-white/45">
                                {{ $search !== '' ? 'No admin accounts matched the current search.' : 'No admin accounts found.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3">
            @include('admin.partials.pagination', ['paginator' => $admins])
        </div>
    </section>
</div>
@endsection
