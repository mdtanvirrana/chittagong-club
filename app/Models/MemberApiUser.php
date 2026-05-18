<?php

namespace App\Models;

use App\Support\MemberAccess;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class MemberApiUser extends Authenticatable
{
    use HasApiTokens;

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

    public function getMemberIdAttribute(): string
    {
        return trim((string) ($this->attributes['PrvcusID'] ?? ''));
    }

    public function getDisplayNameAttribute(): string
    {
        $member = MemberAccess::findActiveMember($this->member_id, [
            'c.Title',
            'c.CusName',
        ]);

        return MemberAccess::displayName($member);
    }
}
