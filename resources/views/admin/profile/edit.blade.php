@extends('layouts.admin')

@section('page_title', 'Profile Settings')
@section('page_eyebrow', 'Account')

@section('content')
<div class="grid gap-4 xl:grid-cols-[minmax(0,1.45fr)_minmax(300px,0.9fr)]">
    <form method="POST" action="{{ route('admin.profile.password.update') }}" class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
        @csrf
        @method('PATCH')

        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="font-display text-lg font-bold text-white">Change Password</h2>
                <p class="mt-1 text-xs text-white/45">Update the password for your current admin account.</p>
            </div>

            <button type="submit" class="inline-flex h-9 items-center justify-center border border-[#30384a] bg-admin-gold px-4 text-sm font-medium text-admin-ink">
                Save Password
            </button>
        </div>

        @if ($errors->any())
            <div class="mt-4 rounded-lg border border-admin-line/12 bg-white/[0.04] px-4 py-3 text-xs text-white/75">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="current_password" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Current Password</label>
                <input
                    id="current_password"
                    name="current_password"
                    type="password"
                    autocomplete="current-password"
                    class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0"
                    placeholder="Enter current password"
                >
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">New Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0"
                    placeholder="Enter new password"
                >
            </div>

            <div>
                <label for="password_confirmation" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Confirm Password</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0"
                    placeholder="Confirm new password"
                >
            </div>
        </div>
    </form>

    <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
        <h2 class="font-display text-lg font-bold text-white">Signed In Account</h2>

        <dl class="mt-4 space-y-3 text-sm text-white/70">
            <div class="flex items-center justify-between gap-3 border-b border-admin-line/10 pb-3">
                <dt class="text-white/45">User ID</dt>
                <dd>{{ $admin?->userid ?? 'N/A' }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3 border-b border-admin-line/10 pb-3">
                <dt class="text-white/45">Display Name</dt>
                <dd class="text-right">{{ $admin?->display_name ?? 'Admin User' }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3">
                <dt class="text-white/45">Admin Access</dt>
                <dd>{{ $admin?->is_admin ? 'Enabled' : 'Disabled' }}</dd>
            </div>
        </dl>
    </section>
</div>
@endsection
