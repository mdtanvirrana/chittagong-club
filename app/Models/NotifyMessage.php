<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NotifyMessage extends Model
{
    protected $table = 'T_Notify';

    protected $primaryKey = 'id_notify_key';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_online' => 'boolean',
        'scheduled_at' => 'datetime',
        'pushed_at' => 'datetime',
        'dtt_added' => 'datetime',
        'dtt_mod' => 'datetime',
        'push_attempts' => 'integer',
        'source_version' => 'integer',
    ];

    public function scopeVisible(Builder $query): Builder
    {
        return $query
            ->where('is_active', 1)
            ->where('is_online', 1)
            ->where(function (Builder $builder) {
                $builder->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now());
            });
    }

    public function scopeWithReadState(Builder $query, string $memberId): Builder
    {
        return $query
            ->leftJoin('T_Notify_Read', function ($join) use ($memberId): void {
                $join
                    ->on('T_Notify_Read.id_notify_key', '=', 'T_Notify.id_notify_key')
                    ->where('T_Notify_Read.member_id', '=', $memberId);
            })
            ->addSelect('T_Notify.*', 'T_Notify_Read.read_at as read_at');
    }

    public function scopeForMember(Builder $query, string $memberId): Builder
    {
        return $query->where(function (Builder $builder) use ($memberId): void {
            $builder
                ->where(function (Builder $allMembers): void {
                    $allMembers
                        ->where('target_type', 'all_members')
                        ->where('target_member_id', '*');
                })
                ->orWhere(function (Builder $memberOnly) use ($memberId): void {
                    $memberOnly
                        ->where('target_type', 'member')
                        ->where('target_member_id', $memberId);
                });
        });
    }

    public function payloadArray(): array
    {
        $payload = json_decode((string) $this->payload, true);

        return is_array($payload) ? $payload : [];
    }
}
