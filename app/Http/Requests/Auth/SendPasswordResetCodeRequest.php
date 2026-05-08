<?php

namespace App\Http\Requests\Auth;

use App\Support\BangladeshMobile;
use Illuminate\Foundation\Http\FormRequest;

class SendPasswordResetCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! BangladeshMobile::normalize((string) $value)) {
                        $fail('Enter a valid Bangladesh mobile number.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Enter your Bangladesh mobile number.',
        ];
    }
}
