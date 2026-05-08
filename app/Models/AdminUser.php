<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
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

    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
        ];
    }

    public static function hashPassword(string $password): string
    {
        return md5($password);
    }

    public function getAuthPassword(): string
    {
        return (string) $this->Password;
    }

    public function passwordMatches(string $password): bool
    {
        $storedPassword = strtolower(trim((string) $this->Password));

        return $storedPassword !== '' && hash_equals(static::hashPassword($password), $storedPassword);
    }

    public function hasAdminAccess(): bool
    {
        return (bool) $this->is_admin;
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
