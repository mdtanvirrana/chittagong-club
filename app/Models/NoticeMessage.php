<?php

namespace App\Models;

use App\Support\PortalContent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NoticeMessage extends Model
{
    protected $table = 'T_MESSAGE';

    protected $primaryKey = 'id_message_key';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'Edate' => 'date',
        'dtt_mod' => 'datetime',
        'dtt_added' => 'datetime',
        'is_active' => 'boolean',
        'is_online' => 'boolean',
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
        return PortalContent::deltaToPlainText($this->tx_post_mgs);
    }

    public function getExcerptAttribute(): string
    {
        return PortalContent::excerpt($this->body_text);
    }

    public function getPublishedDateLabelAttribute(): string
    {
        if ($this->Edate instanceof Carbon) {
            return $this->Edate->format('M d, Y');
        }

        return 'Unknown';
    }

    public function getPublishTimeForFormAttribute(): string
    {
        $time = trim((string) $this->Etime);

        return $time !== '' ? substr($time, 0, 5) : now()->format('H:i');
    }

    public function getImageUrlAttribute(): ?string
    {
        return PortalContent::cleanedOptionalField($this->tx_img_src);
    }

    public function getPostUrlAttribute(): ?string
    {
        return PortalContent::cleanedOptionalField($this->tx_post_url);
    }
}
