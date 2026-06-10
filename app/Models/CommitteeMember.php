<?php

namespace App\Models;

use App\Support\PortalCache;
use Illuminate\Database\Eloquent\Model;

class CommitteeMember extends Model
{
    protected $table = 'T_ORG_COMMITTEE';

    protected $primaryKey = 'id_org_committee_key';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id_org_committee_key' => 'integer',
        'id_org_committee_ver' => 'integer',
        'is_active' => 'boolean',
        'id_ds_env' => 'integer',
        'dtt_mod' => 'datetime',
        'id_user_mod' => 'integer',
        'id_env_key' => 'integer',
        'id_event_key' => 'integer',
        'id_state_key' => 'integer',
        'id_action_key' => 'integer',
        'dtt_added' => 'datetime',
        'Edate' => 'date',
        'id_serial' => 'integer',
        'ct_from_year' => 'integer',
        'ct_to_year' => 'integer',
        'UserSI' => 'integer',
    ];

    public function getMemberIdAttribute(): string
    {
        return trim((string) $this->getAttribute('PrvcusID'));
    }

    public function getMemberNameAttribute(): ?string
    {
        $name = trim((string) $this->getAttribute('CusName'));

        if ($name === '') {
            return null;
        }

        $title = trim((string) $this->getAttribute('Title'));

        return trim(($title !== '' ? $title.' ' : '').$name);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->member_name ?? ($this->member_id !== '' ? $this->member_id : 'Unknown Member');
    }

    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim((string) ($this->member_name ?? $this->member_id))) ?: [];

        $initials = collect(array_values(array_filter($words)))
            ->take(2)
            ->map(fn (string $word): string => strtoupper(mb_substr($word, 0, 1)))
            ->join('');

        return $initials !== '' ? $initials : 'CM';
    }

    public function getPhoneNumberAttribute(): ?string
    {
        foreach (['Mobile', 'Phone'] as $column) {
            $value = trim((string) $this->getAttribute($column));

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    public function getDesignationLabelAttribute(): ?string
    {
        $value = trim((string) $this->getAttribute('tx_designation'));

        return $value !== '' ? $value : null;
    }

    public function getAreaLabelAttribute(): ?string
    {
        $value = trim((string) $this->getAttribute('tx_area'));

        return $value !== '' ? $value : null;
    }

    public function getTermLabelAttribute(): string
    {
        $from = (int) $this->getAttribute('ct_from_year');
        $to = (int) $this->getAttribute('ct_to_year');

        return $from > 0 && $to > 0 ? "{$from} - {$to}" : 'Term not set';
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->member_id === '') {
            return null;
        }

        return PortalCache::memberPhotoThumbUrl($this->member_id)
            ?: PortalCache::memberPhotoUrl($this->member_id);
    }
}
