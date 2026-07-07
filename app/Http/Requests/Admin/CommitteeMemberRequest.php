<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommitteeMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'serial' => trim((string) $this->input('serial')),
            'member_id' => trim((string) $this->input('member_id')),
            'designation' => trim((string) $this->input('designation')),
            'from_year' => trim((string) $this->input('from_year')),
            'to_year' => trim((string) $this->input('to_year')),
            'area' => trim((string) $this->input('area')),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $maximumYear = ((int) now()->format('Y')) + 10;

        return [
            'serial' => ['required', 'integer', 'min:1', 'max:999999'],
            'member_id' => [
                'required',
                'string',
                'max:64',
                Rule::exists('CustomerMst', 'PrvCusID'),
            ],
            'designation' => ['required', 'string', 'max:64'],
            'from_year' => ['required', 'integer', 'min:1800', 'max:'.$maximumYear],
            'to_year' => ['required', 'integer', 'min:1800', 'max:'.$maximumYear, 'gte:from_year'],
            'area' => ['nullable', 'string', 'max:128'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'serial.required' => 'Enter a serial number for display order.',
            'member_id.required' => 'Enter the member ID.',
            'member_id.exists' => 'Select a valid member from CustomerMst.',
            'designation.required' => 'Enter the committee designation.',
            'from_year.required' => 'Enter the term start year.',
            'to_year.required' => 'Enter the term end year.',
            'to_year.gte' => 'The term end year must be the same as or after the start year.',
        ];
    }
}
