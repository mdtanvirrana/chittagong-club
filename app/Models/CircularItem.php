<?php

namespace App\Models;

use App\Support\PortalContent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CircularItem extends Model
{
    protected $table = 'T_CAREER';

    protected $primaryKey = 'id_career_key';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'dtt_mod' => 'datetime',
        'dtt_added' => 'datetime',
        'dtt_ad_start' => 'datetime',
        'dtt_ad_close' => 'datetime',
        'dtt_ad_exp' => 'datetime',
        'is_active' => 'boolean',
        'is_online' => 'boolean',
        'flt_cost' => 'float',
        'flt_cpc' => 'float',
        'flt_cpc_max' => 'float',
        'flt_duration' => 'float',
        'flt_min_salary' => 'float',
        'flt_max_salary' => 'float',
        'ct_click' => 'float',
        'ct_seen' => 'float',
        'ct_interval' => 'integer',
    ];

    public function scopeVisible(Builder $query): Builder
    {
        return $query
            ->where('is_active', 1)
            ->where(function (Builder $builder) {
                $builder->whereNull('is_online')->orWhere('is_online', 1);
            });
    }

    public function getBodyTextAttribute(): string
    {
        return PortalContent::deltaToPlainText($this->tx_body);
    }

    public function getExcerptAttribute(): string
    {
        return PortalContent::excerpt($this->body_text, 180);
    }

    public function getStartDateLabelAttribute(): string
    {
        return $this->dtt_ad_start instanceof Carbon
            ? $this->dtt_ad_start->format('M d, Y g:i A')
            : 'Not scheduled';
    }

    public function getCloseDateLabelAttribute(): string
    {
        return $this->dtt_ad_close instanceof Carbon
            ? $this->dtt_ad_close->format('M d, Y g:i A')
            : 'Open ended';
    }

    public function getActionUrlAttribute(): ?string
    {
        return PortalContent::cleanedOptionalField($this->tx_url);
    }
}
