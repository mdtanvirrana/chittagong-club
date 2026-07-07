<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class NoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'is_online' => $this->boolean('is_online'),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'publish_date' => 'required|date',
            'publish_time' => 'required|date_format:H:i',
            'image_url' => 'nullable|string|max:255',
            'post_url' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
            'is_online' => 'required|boolean',
        ];
    }
}
