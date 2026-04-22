<?php

namespace App\Http\Requests\Member;

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
            'new_password' => ['required', 'string', 'min:6', 'confirmed', 'different:current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Enter your current password.',
            'new_password.required' => 'Enter a new password.',
            'new_password.min' => 'The new password must be at least 6 characters.',
            'new_password.confirmed' => 'Confirm the new password.',
            'new_password.different' => 'The new password must be different from your current password.',
        ];
    }
}
