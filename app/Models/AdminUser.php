<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    public const LOGIN_ID = 'admin';

    protected $table = 'Users_App';

    protected $primaryKey = 'PrvcusID';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $hidden = [
        'Password',
    ];

    protected $rememberTokenName = null;

    public function getAuthPassword(): string
    {
        return (string) $this->Password;
    }

    public function getUseridAttribute(): string
    {
        return trim((string) ($this->attributes['PrvcusID'] ?? $this->attributes['userid'] ?? ''));
    }

    public function setUseridAttribute(string $value): void
    {
        $this->attributes['PrvcusID'] = trim($value);
    }

    public function getUsernameAttribute(): string
    {
        return trim((string) ($this->attributes['username'] ?? $this->userid));
    }

    public function getDisplayNameAttribute(): string
    {
        return trim((string) ($this->username ?: $this->userid));
    }
}
