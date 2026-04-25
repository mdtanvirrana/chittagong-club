@extends('layouts.admin')

@section('page_title', $isEditing ? 'Edit Affiliated Club' : 'Create Affiliated Club')
@section('page_eyebrow', 'Directory')

@section('content')
<form method="POST" action="{{ $isEditing ? route('admin.affiliated-clubs.update', $club->id_affiliated_club_key) : route('admin.affiliated-clubs.store') }}" enctype="multipart/form-data" class="space-y-4">
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
        <a href="{{ route('admin.affiliated-clubs.index') }}" class="inline-flex h-9 items-center gap-2 border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Back to affiliated clubs
        </a>

        <button type="submit" class="inline-flex h-9 items-center justify-center border border-[#30384a] bg-admin-gold px-4 text-sm font-medium text-admin-ink">
            {{ $isEditing ? 'Update Club' : 'Save Club' }}
        </button>
    </div>

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.7fr)_minmax(300px,0.95fr)]">
        <section class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
            <h2 class="font-display text-lg font-bold text-white">Club Details</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="serial" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Serial</label>
                    <input id="serial" name="serial" type="number" min="1" value="{{ old('serial', $club->id_serial) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white focus:border-[#3b4557] focus:ring-0" placeholder="1">
                </div>

                <div class="sm:col-span-2">
                    <label for="company" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Club Name</label>
                    <input id="company" name="company" type="text" value="{{ old('company', $club->COMPANY) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0" placeholder="Affiliated club name">
                </div>

                <div>
                    <label for="branch_name" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Branch Name</label>
                    <input id="branch_name" name="branch_name" type="text" value="{{ old('branch_name', $club->BranchName) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0" placeholder="Branch or location name">
                </div>

                <div>
                    <label for="ceo" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">CEO / Head</label>
                    <input id="ceo" name="ceo" type="text" value="{{ old('ceo', $club->CEO) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0" placeholder="CEO or contact person">
                </div>

                <div class="sm:col-span-2">
                    <label for="branch_address" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Branch Address</label>
                    <textarea id="branch_address" name="branch_address" rows="3" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0" placeholder="Branch address">{{ old('branch_address', $club->BranchAddress) }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label for="ho_address" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Head Office Address</label>
                    <textarea id="ho_address" name="ho_address" rows="3" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0" placeholder="Head office address">{{ old('ho_address', $club->HOAddress) }}</textarea>
                </div>

                <div>
                    <label for="branch_tel" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Branch Telephone</label>
                    <input id="branch_tel" name="branch_tel" type="text" value="{{ old('branch_tel', $club->BranchTel) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0" placeholder="031-1234567">
                </div>

                <div>
                    <label for="ho_tel" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Head Office Telephone</label>
                    <input id="ho_tel" name="ho_tel" type="text" value="{{ old('ho_tel', $club->HOTel) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0" placeholder="02-12345678">
                </div>

                <div>
                    <label for="mobile" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Mobile</label>
                    <input id="mobile" name="mobile" type="text" value="{{ old('mobile', $club->tx_mobile) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0" placeholder="+8801XXXXXXXXX">
                </div>

                <div>
                    <label for="email" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Email</label>
                    <input id="email" name="email" type="text" value="{{ old('email', $club->tx_email) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0" placeholder="info@example.com">
                </div>

                <div>
                    <label for="website" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Website</label>
                    <input id="website" name="website" type="text" value="{{ old('website', $club->tx_url) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0" placeholder="https://example.com">
                </div>

                <div>
                    <label for="fax" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Fax</label>
                    <input id="fax" name="fax" type="text" value="{{ old('fax', $club->tx_fax) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0" placeholder="Fax number">
                </div>

                <div>
                    <label for="vat_registration" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">VAT Registration</label>
                    <input id="vat_registration" name="vat_registration" type="text" value="{{ old('vat_registration', $club->VATREGISTRATION) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0" placeholder="VAT registration">
                </div>

                <div>
                    <label for="shop_id" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Shop ID</label>
                    <input id="shop_id" name="shop_id" type="text" value="{{ old('shop_id', $club->Shopid) }}" class="w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-[#3b4557] focus:ring-0" placeholder="Shop code">
                </div>
            </div>
        </section>

        <section
            x-data="{
                currentUrl: @js($club->display_image_url),
                previewUrl: @js($club->display_image_url),
                previewName: '',
                removeImage: @js((bool) old('remove_image', false)),
                objectUrl: null,
                updatePreview(event) {
                    const [file] = event.target.files || [];

                    if (this.objectUrl) {
                        URL.revokeObjectURL(this.objectUrl);
                        this.objectUrl = null;
                    }

                    if (!file) {
                        this.previewUrl = this.removeImage ? null : this.currentUrl;
                        this.previewName = '';
                        return;
                    }

                    this.removeImage = false;
                    this.objectUrl = URL.createObjectURL(file);
                    this.previewUrl = this.objectUrl;
                    this.previewName = file.name;
                },
                syncRemoval() {
                    if (this.removeImage) {
                        if (this.objectUrl) {
                            URL.revokeObjectURL(this.objectUrl);
                            this.objectUrl = null;
                        }

                        this.$refs.image.value = '';
                        this.previewName = '';
                        this.previewUrl = null;
                        return;
                    }

                    this.previewUrl = this.currentUrl;
                }
            }"
            x-init="if (removeImage) previewUrl = null"
            class="space-y-4"
        >
            <div class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
                <h2 class="font-display text-lg font-bold text-white">Status</h2>
                <div class="mt-4 space-y-2">
                    <label class="flex items-center justify-between border border-admin-line/10 bg-slate-950/20 px-3 py-2.5">
                        <span class="text-sm text-white/78">Active on member panel</span>
                        <input type="checkbox" name="is_active" value="1" class="rounded-none border-[#30384a] bg-transparent text-admin-gold focus:ring-0" @checked(old('is_active', (bool) $club->is_active))>
                    </label>
                </div>
            </div>

            <div class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
                <h2 class="font-display text-lg font-bold text-white">Media</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <label for="image" class="mb-1.5 block text-xs font-medium uppercase tracking-[0.16em] text-white/65">Club Image</label>
                        <input
                            x-ref="image"
                            id="image"
                            name="image"
                            type="file"
                            accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif"
                            @change="updatePreview($event)"
                            class="block w-full border border-[#30384a] bg-slate-950/20 px-3 py-2.5 text-sm text-white file:mr-3 file:border-0 file:bg-admin-gold file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-admin-ink focus:border-[#3b4557] focus:ring-0"
                        >
                        <p class="mt-2 text-xs text-white/45">Files are stored in `public/affiliated_clubs`, while the relative path is saved in `image_path`.</p>
                        <p x-show="previewName" x-text="previewName" class="mt-2 text-xs font-medium text-admin-gold"></p>
                    </div>

                    <label class="flex items-center justify-between border border-admin-line/10 bg-slate-950/20 px-3 py-2.5">
                        <span class="text-sm text-white/78">Remove current image</span>
                        <input type="checkbox" name="remove_image" value="1" x-model="removeImage" @change="syncRemoval()" class="rounded-none border-[#30384a] bg-transparent text-admin-gold focus:ring-0">
                    </label>

                    <div class="overflow-hidden rounded-lg border border-admin-line/10 bg-slate-950/20">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="{{ $club->display_name }}" class="h-48 w-full object-cover">
                        </template>
                        <template x-if="!previewUrl">
                            <div class="flex h-48 flex-col items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-4xl text-admin-gold/70">image</span>
                                <p class="text-xs text-white/45">No image selected</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            @if ($isEditing)
                <div class="rounded-lg border border-admin-line/10 bg-white/[0.03] p-4 shadow-panel">
                    <h2 class="font-display text-lg font-bold text-white">Current Record</h2>
                    <dl class="mt-4 space-y-3 text-sm text-white/70">
                        <div class="flex items-center justify-between gap-3 border-b border-admin-line/10 pb-3">
                            <dt class="text-white/45">Row ID</dt>
                            <dd>#{{ $club->id_affiliated_club_key }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-b border-admin-line/10 pb-3">
                            <dt class="text-white/45">Version</dt>
                            <dd>{{ $club->id_affiliated_club_ver }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-white/45">Stored Image Path</dt>
                            <dd class="max-w-[12rem] truncate text-right">{{ $club->getAttribute('image_path') ?: 'Not set' }}</dd>
                        </div>
                    </dl>
                </div>
            @endif
        </section>
    </div>
</form>
@endsection
