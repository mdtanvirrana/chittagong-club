<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CompanyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'remove_logo' => $this->boolean('remove_logo'),
        ]);
    }

    public function rules(): array
    {
        return [
            'company' => ['required', 'string', 'max:200'],
            'branch_name' => ['nullable', 'string', 'max:200'],
            'ho_address' => ['nullable', 'string', 'max:1000'],
            'ho_tel' => ['nullable', 'string', 'max:1000'],
            'branch_address' => ['nullable', 'string', 'max:1000'],
            'branch_tel' => ['nullable', 'string', 'max:400'],
            'vat_registration' => ['nullable', 'string', 'max:200'],
            'shop_id' => ['nullable', 'string', 'max:50'],
            'ceo' => ['nullable', 'string', 'max:100'],
            'l1' => ['nullable', 'string', 'max:400'],
            'l2' => ['nullable', 'string', 'max:400'],
            'logo_path' => ['nullable', 'string', 'max:400'],
            'club_photo_path' => ['nullable', 'string', 'max:400'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'remove_logo' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'company.required' => 'Enter the company name.',
            'logo.image' => 'Upload a valid logo image file.',
            'logo.max' => 'The logo must be 5 MB or smaller.',
        ];
    }
}
