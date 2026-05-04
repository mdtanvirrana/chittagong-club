<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyProfileRequest;
use App\Support\CompanyProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class CompanyProfileController extends Controller
{
    private const FIELD_DEFINITIONS = [
        'COMPANY' => ['input' => 'company', 'label' => 'Company Name', 'type' => 'text'],
        'BranchName' => ['input' => 'branch_name', 'label' => 'Branch Name', 'type' => 'text'],
        'CEO' => ['input' => 'ceo', 'label' => 'CEO / Profile Title', 'type' => 'text'],
        'HOAddress' => ['input' => 'ho_address', 'label' => 'Head Office Address', 'type' => 'textarea'],
        'HOTel' => ['input' => 'ho_tel', 'label' => 'Head Office Contact', 'type' => 'textarea'],
        'BranchAddress' => ['input' => 'branch_address', 'label' => 'Branch Address', 'type' => 'textarea'],
        'BranchTel' => ['input' => 'branch_tel', 'label' => 'Branch Contact', 'type' => 'text'],
        'VATREGISTRATION' => ['input' => 'vat_registration', 'label' => 'VAT Registration', 'type' => 'text'],
        'Shopid' => ['input' => 'shop_id', 'label' => 'Shop ID', 'type' => 'text'],
        'L1' => ['input' => 'l1', 'label' => 'L1', 'type' => 'text'],
        'L2' => ['input' => 'l2', 'label' => 'L2', 'type' => 'text'],
        'LogoPath' => ['input' => 'logo_path', 'label' => 'Logo Path', 'type' => 'text'],
        'ClubPhotoPath' => ['input' => 'club_photo_path', 'label' => 'Club Photo Path', 'type' => 'text'],
    ];

    public function index(): View
    {
        $record = $this->profileRecord();

        return view('admin.company-profile.index', [
            'profile' => CompanyProfile::current(),
            'fields' => $this->profileFields($record),
        ]);
    }

    public function edit(): View
    {
        $record = $this->profileRecord();

        return view('admin.company-profile.edit', [
            'profile' => CompanyProfile::current(),
            'fields' => $this->profileFields($record),
            'values' => $this->formValues($record),
        ]);
    }

    public function update(CompanyProfileRequest $request): RedirectResponse
    {
        $columns = $this->profileColumns();
        $currentRecord = $this->profileRecord();
        $payload = [];

        foreach (self::FIELD_DEFINITIONS as $column => $definition) {
            if ($column === 'LogoPath' || ! in_array($column, $columns, true)) {
                continue;
            }

            $payload[$column] = $this->nullableInput($request->input($definition['input']));
        }

        if (in_array('LogoPath', $columns, true)) {
            $payload['LogoPath'] = $this->resolveLogoPath(
                $request,
                $this->recordValue($currentRecord, 'LogoPath')
            );
        }

        DB::transaction(function () use ($payload): void {
            $query = DB::table('CPROFILE');

            if ($query->exists()) {
                $query->update($payload);

                return;
            }

            DB::table('CPROFILE')->insert($payload);
        });

        CompanyProfile::clear();

        return redirect()
            ->route('admin.company-profile.index')
            ->with('status', 'Company profile updated successfully.');
    }

    private function profileRecord(): object
    {
        try {
            return DB::table('CPROFILE')->first() ?: (object) [];
        } catch (Throwable) {
            return (object) [];
        }
    }

    private function profileColumns(): array
    {
        try {
            $columns = Schema::getColumnListing('CPROFILE');

            return $columns !== [] ? $columns : array_keys(self::FIELD_DEFINITIONS);
        } catch (Throwable) {
            return array_keys(self::FIELD_DEFINITIONS);
        }
    }

    private function profileFields(object $record): array
    {
        $columns = $this->profileColumns();
        $fields = [];

        foreach (self::FIELD_DEFINITIONS as $column => $definition) {
            if (! in_array($column, $columns, true)) {
                continue;
            }

            $fields[] = [
                'column' => $column,
                'input' => $definition['input'],
                'label' => $definition['label'],
                'type' => $definition['type'],
                'value' => $this->recordValue($record, $column),
            ];
        }

        return $fields;
    }

    private function formValues(object $record): array
    {
        $values = [];

        foreach ($this->profileFields($record) as $field) {
            $values[$field['input']] = $field['value'];
        }

        return $values;
    }

    private function recordValue(object $record, string $column): ?string
    {
        $value = data_get($record, $column);

        if ($value === null) {
            return null;
        }

        return trim((string) $value);
    }

    private function nullableInput(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function resolveLogoPath(Request $request, ?string $currentPath): ?string
    {
        if ($request->boolean('remove_logo') && ! $request->hasFile('logo')) {
            $this->deleteManagedLogo($currentPath);

            return null;
        }

        if (! $request->hasFile('logo')) {
            $logoPath = $this->nullableInput($request->input('logo_path'));

            if ($logoPath !== trim((string) $currentPath)) {
                $this->deleteManagedLogo($currentPath);
            }

            return $logoPath;
        }

        $logo = $request->file('logo');

        if ($logo === null || ! $logo->isValid()) {
            return $this->nullableInput($request->input('logo_path')) ?: $currentPath;
        }

        $directory = public_path('company_profile');
        File::ensureDirectoryExists($directory);

        $extension = strtolower($logo->getClientOriginalExtension() ?: $logo->extension() ?: 'png');
        $filename = sprintf('company-logo-%s-%s.%s', now()->format('YmdHis'), Str::lower(Str::random(8)), $extension);

        $logo->move($directory, $filename);
        $this->deleteManagedLogo($currentPath);

        return 'company_profile/' . $filename;
    }

    private function deleteManagedLogo(?string $path): void
    {
        $path = trim((string) $path);

        if ($path === '' || $path === '0' || $path === '?') {
            return;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $urlPath = parse_url($path, PHP_URL_PATH);
            $path = is_string($urlPath) ? ltrim($urlPath, '/') : '';
        } else {
            $path = ltrim($path, '/');
        }

        if ($path === '' || ! str_starts_with($path, 'company_profile/')) {
            return;
        }

        $fullPath = public_path($path);

        if (is_file($fullPath)) {
            File::delete($fullPath);
        }
    }
}
