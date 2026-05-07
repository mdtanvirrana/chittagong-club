@extends('layouts.admin')

@section('page_title', $isEditing ? 'Edit Admin Account' : 'Create Admin Account')
@section('page_eyebrow', 'Access Control')

@section('content')
<form method="POST" action="{{ $isEditing ? route('admin.admin-users.update', $admin->userid) : route('admin.admin-users.store') }}" class="space-y-4">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-admin-line/12 bg-white/[0.04] px-4 py-3 text-xs text-white/75">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('admin.admin-users.index') }}" class="inline-flex h-9 items-center gap-2 border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Back to admin accounts
        </a>

        <button type="submit" class="inline-flex h-9 items-center justify-center border border-[#30384a] bg-admin-gold px-4 text-sm font-medium text-admin-ink">
            {{ $isEditing ? 'Update Admin' : 'Save Admin' }}
        </button>
    </div>

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.5fr)_minmax(300px,0.9fr)]">
        <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
            <h2 class="font-display text-lg font-bold text-white">Account Details</h2>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="user_id" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">User ID</label>
                    <input
                        id="user_id"
                        name="user_id"
                        type="text"
                        value="{{ old('user_id', $admin->userid) }}"
                        class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0"
                        placeholder="admin"
                    >
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0"
                        placeholder="{{ $isEditing ? 'Leave blank to keep current' : 'Enter password' }}"
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
                        placeholder="Confirm password"
                    >
                </div>

                <label class="sm:col-span-2 flex items-start gap-3 rounded-lg border border-admin-line/20 bg-slate-950/20 px-3 py-3">
                    <input
                        type="checkbox"
                        name="is_admin"
                        value="1"
                        @checked((bool) old('is_admin', $admin->is_admin))
                        class="mt-0.5 rounded border-[#30384a] bg-white/[0.04] text-admin-gold focus:ring-admin-gold/30"
                    >
                    <span>
                        <span class="block text-sm font-semibold text-white">Allow Admin Panel Login</span>
                        <span class="mt-1 block text-xs leading-5 text-white/45">If unchecked, this account cannot sign in to the admin panel.</span>
                    </span>
                </label>
            </div>
        </section>

        <section class="space-y-4">
            <div class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
                <h2 class="font-display text-lg font-bold text-white">Rules</h2>
                <div class="mt-4 space-y-3 text-sm leading-6 text-white/65">
                    <p>Admin login checks the `Users_App` row by user ID and requires `is_admin = 1`.</p>
                    <p>Admin passwords are saved with the same MD5 format used by the current `Users_App` credentials.</p>
                    <p>Admin accounts are blocked from member-panel sign-in while `is_admin = 1`.</p>
                </div>
            </div>

            @if ($isEditing)
                <div class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
                    <h2 class="font-display text-lg font-bold text-white">Current Record</h2>
                    <dl class="mt-4 space-y-3 text-sm text-white/70">
                        <div class="flex items-center justify-between gap-3 border-b border-admin-line/10 pb-3">
                            <dt class="text-white/45">User ID</dt>
                            <dd>{{ $admin->userid }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-white/45">Admin Access</dt>
                            <dd>{{ $admin->is_admin ? 'Enabled' : 'Disabled' }}</dd>
                        </div>
                    </dl>
                </div>
            @endif
        </section>
    </div>
</form>
@endsection
