@extends('layouts.admin')

@section('page_title', $isEditing ? $section['pageTitleEdit'] : $section['pageTitleCreate'])
@section('page_eyebrow', $section['eyebrow'])

@section('content')
<form method="POST" action="{{ $isEditing ? route($section['routePrefix'].'.update', $record->id_org_committee_key) : route($section['routePrefix'].'.store') }}" class="space-y-4">
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
        <a href="{{ route($section['routePrefix'].'.index') }}" class="inline-flex h-9 items-center gap-2 border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            {{ $section['backLabel'] }}
        </a>

        <button type="submit" class="inline-flex h-9 items-center justify-center border border-[#30384a] bg-admin-gold px-4 text-sm font-medium text-admin-ink">
            {{ $isEditing ? $section['updateLabel'] : $section['saveLabel'] }}
        </button>
    </div>

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.55fr)_minmax(300px,0.95fr)]">
        <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
            <h2 class="font-display text-lg font-bold text-white">Record Details</h2>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="serial" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Serial</label>
                    <input id="serial" name="serial" type="number" min="1" value="{{ old('serial', $record->id_serial) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white focus:border-[#3b4557] focus:ring-0" placeholder="1">
                </div>

                <div>
                    <label for="member_id" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Member ID</label>
                    <input
                        id="member_id"
                        name="member_id"
                        type="text"
                        list="committeeMemberList"
                        value="{{ old('member_id', $record->member_id) }}"
                        class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0"
                        placeholder="A-0001"
                        autocomplete="off"
                    >

                    <datalist id="committeeMemberList">
                        @foreach ($memberOptions as $member)
                            <option value="{{ $member['id'] }}">{{ $member['name'] }}{{ $member['phone'] ? ' - '.$member['phone'] : '' }}</option>
                        @endforeach
                    </datalist>
                </div>

                <div class="sm:col-span-2">
                    <label for="designation" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Designation</label>
                    <input
                        id="designation"
                        name="designation"
                        type="text"
                        value="{{ old('designation', $record->tx_designation) }}"
                        class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0 {{ $section['lockDesignation'] ? 'opacity-80' : '' }}"
                        placeholder="Chairman"
                        {{ $section['lockDesignation'] ? 'readonly' : '' }}
                    >
                </div>

                <div>
                    <label for="from_year" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">From Year</label>
                    <input id="from_year" name="from_year" type="number" min="1800" value="{{ old('from_year', $record->ct_from_year) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white focus:border-[#3b4557] focus:ring-0" placeholder="{{ now()->format('Y') }}">
                </div>

                <div>
                    <label for="to_year" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">To Year</label>
                    <input id="to_year" name="to_year" type="number" min="1800" value="{{ old('to_year', $record->ct_to_year) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white focus:border-[#3b4557] focus:ring-0" placeholder="{{ ((int) now()->format('Y')) + 1 }}">
                </div>

                <div class="sm:col-span-2">
                    <label for="area" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Area / Description</label>
                    <textarea id="area" name="area" rows="4" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0" placeholder="Administration, finance, facilities">{{ old('area', $record->tx_area) }}</textarea>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
                <h2 class="font-display text-lg font-bold text-white">Status</h2>

                <div class="mt-4 space-y-2">
                    <label class="flex items-center justify-between border border-admin-line/10 bg-slate-950/20 px-3 py-2.5">
                        <span class="text-sm text-white/78">Active on member panel</span>
                        <input type="checkbox" name="is_active" value="1" class="rounded-none border-[#30384a] bg-transparent text-admin-gold focus:ring-0" @checked(old('is_active', (bool) $record->is_active))>
                    </label>
                </div>
            </div>

            <div class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
                <h2 class="font-display text-lg font-bold text-white">Selected Member</h2>

                @if ($selectedMember)
                    <dl class="mt-4 space-y-3 text-sm text-white/70">
                        <div class="flex items-center justify-between gap-3 border-b border-admin-line/10 pb-3">
                            <dt class="text-white/45">Member ID</dt>
                            <dd>{{ $selectedMember['id'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-b border-admin-line/10 pb-3">
                            <dt class="text-white/45">Name</dt>
                            <dd class="text-right">{{ $selectedMember['name'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-white/45">Phone</dt>
                            <dd>{{ $selectedMember['phone'] ?? 'No phone' }}</dd>
                        </div>
                    </dl>
                @else
                    <div class="mt-4 rounded-lg border border-admin-line/10 bg-slate-950/20 px-3 py-4 text-sm text-white/45">
                        Enter an existing CustomerMst member ID to attach the row to a member profile.
                    </div>
                @endif
            </div>

            @if ($isEditing)
                <div class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
                    <h2 class="font-display text-lg font-bold text-white">Current Record</h2>
                    <dl class="mt-4 space-y-3 text-sm text-white/70">
                        <div class="flex items-center justify-between gap-3 border-b border-admin-line/10 pb-3">
                            <dt class="text-white/45">Row ID</dt>
                            <dd>#{{ $record->id_org_committee_key }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-b border-admin-line/10 pb-3">
                            <dt class="text-white/45">Version</dt>
                            <dd>{{ $record->id_org_committee_ver }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-b border-admin-line/10 pb-3">
                            <dt class="text-white/45">Stored Member ID</dt>
                            <dd>{{ $record->member_id ?: 'Not set' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-white/45">Term</dt>
                            <dd>{{ $record->term_label }}</dd>
                        </div>
                    </dl>
                </div>
            @endif
        </section>
    </div>
</form>
@endsection
