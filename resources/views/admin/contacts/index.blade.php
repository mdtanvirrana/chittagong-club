@extends('layouts.admin')

@section('page_title', 'Contacts')
@section('page_eyebrow', 'Directory')

@section('content')
<div class="space-y-4">
    <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[10px] uppercase tracking-[0.24em] text-white/35">Contact Manager</p>
                <h2 class="mt-1 font-display text-xl font-bold text-white">Manage Contact Directory</h2>
                <p class="mt-1 text-xs text-white/45">Departments are sourced from `List_Department`, with current legacy contact labels kept editable.</p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <form
                    method="GET"
                    action="{{ route('admin.contacts.index') }}"
                    x-data="{
                        hierarchy: @js($hierarchy),
                        department: @js($department),
                        subDepartment: @js($subDepartment),
                        timer: null,
                        get subDepartmentOptions() {
                            return this.hierarchy[this.department] ?? [];
                        },
                        queueSearch() {
                            clearTimeout(this.timer);
                            this.timer = setTimeout(() => this.$el.requestSubmit(), 350);
                        },
                        submitFilters() {
                            if (! this.subDepartmentOptions.includes(this.subDepartment)) {
                                this.subDepartment = '';
                            }

                            this.$nextTick(() => this.$el.requestSubmit());
                        }
                    }"
                    class="grid gap-2 sm:grid-cols-[minmax(14rem,1fr)_11rem_11rem] lg:min-w-[44rem]"
                >
                    <label class="flex items-center gap-2 border border-[#30384a] bg-slate-950/20 px-3 py-2">
                        <span class="material-symbols-outlined text-[18px] text-white/35">search</span>
                        <input
                            type="text"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Search department, sub department, phone or email"
                            @input="queueSearch()"
                            class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-white placeholder:text-white/25 focus:ring-0"
                        >
                    </label>

                    <label class="border border-[#30384a] bg-slate-950/20 px-3 py-2">
                        <select
                            name="department"
                            x-model="department"
                            @change="submitFilters()"
                            class="w-full border-0 bg-transparent p-0 text-sm text-white focus:ring-0"
                        >
                            <option value="">All departments</option>
                            @foreach ($departments as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="border border-[#30384a] bg-slate-950/20 px-3 py-2">
                        <select
                            name="sub_department"
                            x-model="subDepartment"
                            @change="$el.form.requestSubmit()"
                            :disabled="subDepartmentOptions.length === 0"
                            class="w-full border-0 bg-transparent p-0 text-sm text-white disabled:text-white/25 focus:ring-0"
                        >
                            <option value="">All sub departments</option>
                            <template x-for="option in subDepartmentOptions" :key="option">
                                <option :value="option" x-text="option"></option>
                            </template>
                        </select>
                    </label>
                </form>

                <div class="flex gap-2">
                    @if ($search !== '' || $department !== '' || $subDepartment !== '')
                        <a href="{{ route('admin.contacts.index') }}" class="inline-flex h-10 items-center justify-center border border-[#30384a] px-4 text-sm text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                            Clear
                        </a>
                    @endif

                    <a href="{{ route('admin.contacts.create') }}" class="inline-flex h-10 items-center justify-center border border-[#30384a] bg-admin-gold px-4 text-sm font-medium text-admin-ink">
                        Add Contact
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-admin-line/10 bg-white/[0.03] shadow-panel">
        <div class="flex items-center justify-between border-b border-admin-line/10 px-4 py-3">
            <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">Contact List</p>
            <p class="text-xs text-white/45">{{ $contacts->total() }} records</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-admin-line/10 bg-slate-950/20 text-[10px] uppercase tracking-[0.2em] text-white/35">
                    <tr>
                        <th class="px-4 py-3 font-medium">Department</th>
                        <th class="px-4 py-3 font-medium">Sub Department</th>
                        <th class="px-4 py-3 font-medium">Phone</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Group</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-admin-line/10">
                    @forelse ($contacts as $contact)
                        <tr class="align-top">
                            <td class="px-4 py-3.5">
                                <p class="font-semibold text-white">{{ $contact->department_name }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-white/65">{{ $contact->sub_department_name ?? 'General' }}</td>
                            <td class="px-4 py-3.5 text-white/65">{{ $contact->phone_number ?? 'N/A' }}</td>
                            <td class="px-4 py-3.5 text-white/65">{{ $contact->email_address ?? 'N/A' }}</td>
                            <td class="px-4 py-3.5 text-xs text-white/45">#{{ $contact->group_id ?? 'N/A' }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('admin.contacts.edit', $contact->Sl) }}" class="inline-flex h-8 items-center border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.contacts.destroy', $contact->Sl) }}" onsubmit="return confirm('Delete this contact?');">
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
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-white/45">
                                {{ $search !== '' || $department !== '' || $subDepartment !== '' ? 'No contacts matched the current filters.' : 'No contacts found.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3">
            @include('admin.partials.pagination', ['paginator' => $contacts])
        </div>
    </section>
</div>
@endsection
