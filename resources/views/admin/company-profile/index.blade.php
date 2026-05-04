@extends('layouts.admin')

@section('page_title', 'Company Profile')
@section('page_eyebrow', 'Settings')

@section('content')
<div class="space-y-4">
    <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4">
                <img src="{{ $profile['logoUrl'] }}" alt="{{ $profile['companyName'] }}" class="size-16 rounded-lg border border-admin-line/20 bg-white object-contain p-2">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.24em] text-white/35">Company Profile</p>
                    <h2 class="mt-1 font-display text-xl font-bold text-white">{{ $profile['companyName'] }}</h2>
                    <p class="mt-1 text-xs text-white/45">{{ $profile['companyAddressText'] ?: 'No address saved' }}</p>
                </div>
            </div>

            <a href="{{ route('admin.company-profile.edit') }}" class="inline-flex h-10 items-center justify-center gap-2 border border-[#30384a] bg-admin-gold px-4 text-sm font-medium text-admin-ink">
                <span class="material-symbols-outlined text-[18px]">edit</span>
                Edit Profile
            </a>
        </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-admin-line/10 bg-white/[0.03] shadow-panel">
        <div class="flex items-center justify-between border-b border-admin-line/10 px-4 py-3">
            <p class="text-[10px] uppercase tracking-[0.2em] text-white/35">CPROFILE Fields</p>
            <p class="text-xs text-white/45">{{ count($fields) }} fields</p>
        </div>

        <dl class="divide-y divide-admin-line/10">
            @foreach ($fields as $field)
                <div class="grid gap-2 px-4 py-3.5 sm:grid-cols-[13rem_minmax(0,1fr)]">
                    <dt>
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-white/55">{{ $field['label'] }}</p>
                        <p class="mt-1 text-[11px] text-white/35">{{ $field['column'] }}</p>
                    </dt>
                    <dd class="whitespace-pre-line break-words text-sm text-white/72">{{ filled($field['value']) ? $field['value'] : 'N/A' }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
</div>
@endsection
