<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AffiliatedClubRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'remove_image' => $this->boolean('remove_image'),
            'remove_logo' => $this->boolean('remove_logo'),
        ]);
    }

    public function rules(): array
    {
        return [
            'serial' => ['required', 'integer', 'min:1'],
            'company' => ['required', 'string', 'max:200'],
            'branch_name' => ['nullable', 'string', 'max:200'],
            'branch_address' => ['nullable', 'string', 'max:400'],
            'ho_address' => ['nullable', 'string', 'max:400'],
            'branch_tel' => ['nullable', 'string', 'max:200'],
            'ho_tel' => ['nullable', 'string', 'max:200'],
            'mobile' => ['nullable', 'string', 'max:400'],
            'email' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'string', 'max:400'],
            'fax' => ['nullable', 'string', 'max:400'],
            'ceo' => ['nullable', 'string', 'max:100'],
            'vat_registration' => ['nullable', 'string', 'max:200'],
            'shop_id' => ['nullable', 'string', 'max:10'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'remove_logo' => ['required', 'boolean'],
            'remove_image' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'serial.required' => 'Enter a serial number for display order.',
            'company.required' => 'Enter the affiliated club name.',
            'logo.image' => 'Upload a valid logo image file.',
            'logo.max' => 'The logo must be 5 MB or smaller.',
            'image.image' => 'Upload a valid image file.',
            'image.max' => 'The image must be 5 MB or smaller.',
        ];
    }
}
