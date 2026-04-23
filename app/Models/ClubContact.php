<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubContact extends Model
{
    protected $table = 'C_Contacts';

    protected $primaryKey = 'Sl';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'Sl' => 'integer',
    ];

    public function getGroupIdAttribute(): ?int
    {
        $value = trim((string) $this->getAttribute('Contact_ID'));

        return preg_match('/^\d+$/', $value) === 1 ? (int) $value : null;
    }

    public function getDepartmentNameAttribute(): string
    {
        return trim((string) $this->getAttribute('Contact_Dept'));
    }

    public function getSubDepartmentNameAttribute(): ?string
    {
        $value = trim((string) $this->getAttribute('Contact_Sub_Dept'));

        return $value !== '' ? $value : null;
    }

    public function getPhoneNumberAttribute(): ?string
    {
        $value = trim((string) $this->getAttribute('Phone'));

        return $value !== '' ? $value : null;
    }

    public function getEmailAddressAttribute(): ?string
    {
        $value = trim((string) $this->getAttribute('Email'));

        return $value !== '' ? $value : null;
    }
}
