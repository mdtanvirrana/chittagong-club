<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_admin' => $this->boolean('is_admin'),
        ]);
    }

    public function rules(): array
    {
        $passwordRule = $this->isMethod('post') ? 'required' : 'nullable';

        return [
            'user_id' => ['required', 'string', 'max:40'],
            'password' => [$passwordRule, 'string', 'min:6', 'max:40', 'confirmed'],
            'is_admin' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Enter an admin user ID.',
            'user_id.max' => 'The admin user ID may not be greater than 40 characters.',
            'password.required' => 'Enter a password.',
            'password.min' => 'The password must be at least 6 characters.',
            'password.max' => 'The password may not be greater than 40 characters.',
            'password.confirmed' => 'Confirm the password.',
        ];
    }
}
