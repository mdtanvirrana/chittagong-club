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
            'remove_club_photo' => $this->boolean('remove_club_photo'),
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
            'logo_path' => ['nullable', 'string', 'max:400'],
            'club_photo_path' => ['nullable', 'string', 'max:400'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'club_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'remove_logo' => ['required', 'boolean'],
            'remove_club_photo' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'company.required' => 'Enter the company name.',
            'logo.image' => 'Upload a valid logo image file.',
            'logo.max' => 'The logo must be 5 MB or smaller.',
            'club_photo.image' => 'Upload a valid club photo image file.',
            'club_photo.max' => 'The club photo must be 5 MB or smaller.',
        ];
    }
}
