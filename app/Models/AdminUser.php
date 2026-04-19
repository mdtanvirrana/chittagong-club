<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    protected $table = 'Users';

    protected $primaryKey = 'userid';

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

    public function getDisplayNameAttribute(): string
    {
        return trim((string) ($this->username ?: $this->userid));
    }
}
