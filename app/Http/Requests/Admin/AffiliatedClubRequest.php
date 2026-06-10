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
        $logoRule = $this->isMethod('post')
            ? ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048']
            : ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'];

        return [
            'serial' => ['required', 'integer', 'min:1', 'max:999999'],

            'country' => ['nullable', 'string', 'max:100'],
            'company' => ['required', 'string', 'max:255'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'ceo' => ['nullable', 'string', 'max:255'],

            'branch_address' => ['nullable', 'string', 'max:500'],
            'ho_address' => ['nullable', 'string', 'max:500'],

            'branch_tel' => ['nullable', 'string', 'max:50'],
            'ho_tel' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],

            'email' => ['nullable', 'email', 'max:100'],
            'website' => ['nullable', 'url', 'max:150'],
            'fax' => ['nullable', 'string', 'max:50'],

            'vat_registration' => ['nullable', 'string', 'max:100'],
            'shop_id' => ['nullable', 'string', 'max:8'],

            'is_active' => ['nullable', 'boolean'],

            'logo' => $logoRule,

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:4096'
            ],

            'remove_logo' => ['nullable', 'boolean'],
            'remove_image' => ['nullable', 'boolean'],
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
