@extends('layouts.admin')

@section('page_title', $isEditing ? 'Edit Contact' : 'Create Contact')
@section('page_eyebrow', 'Directory')

@section('content')
<form method="POST" action="{{ $isEditing ? route('admin.contacts.update', $contact->Sl) : route('admin.contacts.store') }}" class="space-y-4">
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
        <a href="{{ route('admin.contacts.index') }}" class="inline-flex h-9 items-center gap-2 border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Back to contacts
        </a>

        <button type="submit" class="inline-flex h-9 items-center justify-center border border-[#30384a] bg-admin-gold px-4 text-sm font-medium text-admin-ink">
            {{ $isEditing ? 'Update Contact' : 'Save Contact' }}
        </button>
    </div>

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.55fr)_minmax(300px,0.95fr)]">
        <section
            x-data="{
                hierarchy: @js($hierarchy),
                department: @js(old('department', $contact->department_name)),
                subDepartment: @js(old('sub_department', $contact->sub_department_name)),
                get subDepartmentOptions() {
                    return this.hierarchy[this.department] ?? [];
                },
                syncSubDepartment() {
                    if (! this.subDepartmentOptions.includes(this.subDepartment)) {
                        this.subDepartment = '';
                    }
                }
            }"
            class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel"
        >
            <h2 class="font-display text-lg font-bold text-white">Contact Details</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="department" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Department</label>
                    <select
                        id="department"
                        name="department"
                        x-model="department"
                        @change="syncSubDepartment()"
                        class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white focus:border-[#3b4557] focus:ring-0"
                    >
                        <option value="">Select department</option>
                        @foreach ($departments as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-white/45">Pulled from the live department table and augmented with current legacy contact labels.</p>
                </div>

                <div class="sm:col-span-2">
                    <label for="sub_department" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Sub Department</label>
                    <select
                        id="sub_department"
                        name="sub_department"
                        x-model="subDepartment"
                        :disabled="subDepartmentOptions.length === 0"
                        class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white disabled:text-white/25 focus:border-[#3b4557] focus:ring-0"
                    >
                        <option value="">No sub department</option>
                        <template x-for="option in subDepartmentOptions" :key="option">
                            <option :value="option" x-text="option"></option>
                        </template>
                    </select>
                    <p class="mt-2 text-xs text-white/45">Leave blank for a direct department contact line.</p>
                </div>

                <div>
                    <label for="phone" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Phone</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $contact->phone_number) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0" >
                </div>

                <div>
                    <label for="email" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $contact->email_address) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0" >
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
                <h2 class="font-display text-lg font-bold text-white">Guidance</h2>
                <div class="mt-4 space-y-3 text-sm leading-6 text-white/65">
                    <p>Each row is one phone or email entry shown on the user contact page.</p>
                    <p>Contacts under the same department are grouped together automatically on the public page.</p>
                    <p>Saving, updating, or deleting a contact invalidates the cached member contact directory immediately.</p>
                </div>
            </div>

            @if ($isEditing)
                <div class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
                    <h2 class="font-display text-lg font-bold text-white">Current Record</h2>
                    <dl class="mt-4 space-y-3 text-sm text-white/70">
                        <div class="flex items-center justify-between gap-3 border-b border-admin-line/10 pb-3">
                            <dt class="text-white/45">Row ID</dt>
                            <dd>#{{ $contact->Sl }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-b border-admin-line/10 pb-3">
                            <dt class="text-white/45">Group</dt>
                            <dd>#{{ $contact->group_id ?? 'N/A' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-white/45">Department</dt>
                            <dd class="text-right">{{ $contact->department_name }}</dd>
                        </div>
                    </dl>
                </div>
            @endif
        </section>
    </div>
</form>
@endsection
