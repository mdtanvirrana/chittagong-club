<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'department' => trim((string) $this->input('department')),
            'sub_department' => trim((string) $this->input('sub_department')),
            'phone' => trim((string) $this->input('phone')),
            'email' => trim((string) $this->input('email')),
        ]);
    }

    public function rules(): array
    {
        return [
            'department' => 'required|string|max:20',
            'sub_department' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20|required_without:email',
            'email' => 'nullable|email:rfc|max:50|required_without:phone',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required_without' => 'Add at least a phone number or an email address.',
            'email.required_without' => 'Add at least a phone number or an email address.',
        ];
    }
}
