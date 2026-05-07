<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'max:40', 'confirmed', 'different:current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Enter your current password.',
            'password.required' => 'Enter a new password.',
            'password.min' => 'The new password must be at least 6 characters.',
            'password.max' => 'The new password may not be greater than 40 characters.',
            'password.confirmed' => 'Confirm the new password.',
            'password.different' => 'The new password must be different from your current password.',
        ];
    }
}
