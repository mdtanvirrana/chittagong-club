@extends('layouts.admin')

@section('page_title', 'Edit Company Profile')
@section('page_eyebrow', 'Settings')

@section('content')
<form
    method="POST"
    action="{{ route('admin.company-profile.update') }}"
    enctype="multipart/form-data"
    class="space-y-4"
    x-data="{
        currentLogoUrl: @js($profile['logoUrl']),
        previewLogoUrl: @js($profile['logoUrl']),
        logoPreviewName: '',
        removeLogo: @js((bool) old('remove_logo', false)),
        logoObjectUrl: null,
        updatePreview(event) {
            const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;

            if (this.logoObjectUrl) {
                URL.revokeObjectURL(this.logoObjectUrl);
                this.logoObjectUrl = null;
            }

            if (! file) {
                this.previewLogoUrl = this.removeLogo ? '' : this.currentLogoUrl;
                this.logoPreviewName = '';
                return;
            }

            this.logoObjectUrl = URL.createObjectURL(file);
            this.previewLogoUrl = this.logoObjectUrl;
            this.logoPreviewName = file.name;
            this.removeLogo = false;
        },
        syncRemoval() {
            if (this.removeLogo) {
                this.previewLogoUrl = '';
                this.logoPreviewName = '';

                if (this.$refs.logo) {
                    this.$refs.logo.value = '';
                }

                return;
            }

            this.previewLogoUrl = this.currentLogoUrl;
        }
    }"
>
    @csrf
    @method('PUT')

    @if ($errors->any())
        <div class="rounded-lg border border-admin-line/12 bg-white/[0.04] px-4 py-3 text-xs text-white/75">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('admin.company-profile.index') }}" class="inline-flex h-9 items-center gap-2 border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Back to profile
        </a>

        <button type="submit" class="inline-flex h-9 items-center justify-center border border-[#30384a] bg-admin-gold px-4 text-sm font-medium text-admin-ink">
            Update Company Profile
        </button>
    </div>

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.45fr)_minmax(300px,0.95fr)]">
        <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
            <h2 class="font-display text-lg font-bold text-white">Profile Information</h2>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                @foreach ($fields as $field)
                    @continue($field['column'] === 'LogoPath')

                    @php
                        $inputValue = old($field['input'], $values[$field['input']] ?? '');
                        $wide = in_array($field['type'], ['textarea'], true) || in_array($field['column'], ['COMPANY', 'BranchName', 'ClubPhotoPath'], true);
                    @endphp

                    <div class="{{ $wide ? 'sm:col-span-2' : '' }}">
                        <label for="{{ $field['input'] }}" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">{{ $field['label'] }}</label>
                        @if ($field['type'] === 'textarea')
                            <textarea id="{{ $field['input'] }}" name="{{ $field['input'] }}" rows="4" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0">{{ $inputValue }}</textarea>
                        @else
                            <input id="{{ $field['input'] }}" name="{{ $field['input'] }}" type="text" value="{{ $inputValue }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0">
                        @endif
                        <p class="mt-2 text-[11px] text-white/35">{{ $field['column'] }}</p>
                    </div>
                @endforeach

                <input type="hidden" name="logo_path" value="{{ old('logo_path', $values['logo_path'] ?? '') }}">
            </div>
        </section>

        <section class="space-y-4">
            <div class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
                <h2 class="font-display text-lg font-bold text-white">Company Logo</h2>

                <div class="mt-4 overflow-hidden rounded-lg border border-admin-line/10 bg-slate-950/20">
                    <template x-if="previewLogoUrl">
                        <img :src="previewLogoUrl" alt="{{ $profile['companyName'] }} logo" class="h-48 w-full object-contain bg-white p-4">
                    </template>
                    <template x-if="!previewLogoUrl">
                        <div class="flex h-48 items-center justify-center text-sm text-white/35">No logo selected</div>
                    </template>
                </div>

                <div class="mt-4">
                    <label for="logo" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Upload Logo</label>
                    <input
                        x-ref="logo"
                        id="logo"
                        name="logo"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif"
                        @change="updatePreview($event)"
                        class="block w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white file:mr-3 file:border-0 file:bg-admin-gold file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-admin-ink focus:border-[#3b4557] focus:ring-0"
                    >
                    <p x-show="logoPreviewName" x-text="logoPreviewName" class="mt-2 text-xs font-medium text-admin-gold"></p>
                </div>

                <input type="hidden" name="remove_logo" value="0">
                <label class="mt-4 flex items-center justify-between rounded-lg border border-admin-line/10 bg-slate-950/20 px-3 py-3">
                    <span class="text-sm text-white/78">Remove current logo</span>
                    <input type="checkbox" name="remove_logo" value="1" x-model="removeLogo" @change="syncRemoval()" class="rounded-none border-[#30384a] bg-transparent text-admin-gold focus:ring-0">
                </label>
            </div>

            <div class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
                <h2 class="font-display text-lg font-bold text-white">Logo Path</h2>
                <p class="mt-3 break-words text-sm text-white/65">{{ $values['logo_path'] ?? 'Default public/logo.png' }}</p>
            </div>
        </section>
    </div>
</form>
@endsection
