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

    <div class="grid gap-4">
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
            <label for="is_admin" class="sm:col-span-2 flex items-center justify-between rounded-lg border border-admin-line/20 bg-slate-950/20 px-4 py-3 cursor-pointer">
                
                <div>
                    <p class="text-sm font-semibold text-white">Allow Admin Panel Login</p>
                    <p class="text-xs text-black">Enable access to the admin dashboard</p>
                </div>
            
                <div class="relative">
                    <input type="hidden" name="is_admin" value="false">
            
                    <input
                        type="checkbox"
                        id="is_admin"
                        name="is_admin"
                        value="true"
                        @checked(old('is_admin', $admin->is_admin ?? false))
                        class="peer sr-only"
                    >
            
                    <!-- Switch Background -->
                    <div class="h-6 w-11 rounded-full bg-[#30384a] transition peer-checked:bg-admin-gold"></div>
            
                    <!-- Switch Knob -->
                    <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition-transform duration-200 peer-checked:translate-x-5"></div>
                </div>
            </label>
            </div>
        </section>
    </div>
</form>
@endsection
