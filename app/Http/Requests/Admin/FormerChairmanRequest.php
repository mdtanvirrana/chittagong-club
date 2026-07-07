<?php

namespace App\Http\Requests\Admin;

class FormerChairmanRequest extends CommitteeMemberRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $this->merge([
            'designation' => 'Chairman',
        ]);
    }
}
