<?php

namespace App\Models;

use App\Support\ImageVariants;
use App\Support\PortalContent;
use Illuminate\Database\Eloquent\Model;

class AffiliatedClub extends Model
{
    protected $table = 'T_AFFILIATED_CLUBS';

    protected $primaryKey = 'id_affiliated_club_key';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id_affiliated_club_key' => 'integer',
        'id_affiliated_club_ver' => 'integer',
        'id_serial' => 'integer',
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
    ];

    public function getCompanyNameAttribute(): string
    {
        return trim((string) $this->getAttribute('COMPANY'));
    }

    public function getBranchNameLabelAttribute(): ?string
    {
        $value = trim((string) $this->getAttribute('BranchName'));

        return $value !== '' ? $value : null;
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->company_name !== '') {
            return $this->company_name;
        }

        return $this->branch_name_label ?? '—';
    }

    public function getDisplayAddressAttribute(): ?string
    {
        foreach (['BranchAddress', 'HOAddress'] as $column) {
            $value = trim((string) $this->getAttribute($column));

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->publicAssetUrl($this->getAttribute('image_path'));
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->publicAssetUrl($this->getAttribute('Logo_Path'));
    }

    public function getDisplayImageUrlAttribute(): ?string
    {
        return $this->image_url;
    }

    public function getDisplayImageThumbUrlAttribute(): ?string
    {
        return ImageVariants::urlForPath($this->getAttribute('image_path'), 640, 360) ?: $this->display_image_url;
    }

    public function getDisplayLogoUrlAttribute(): ?string
    {
        return $this->logo_url;
    }

    public function getDisplayLogoThumbUrlAttribute(): ?string
    {
        return ImageVariants::urlForPath($this->getAttribute('Logo_Path'), 160, 160) ?: $this->display_logo_url;
    }

    private function publicAssetUrl(mixed $path): ?string
    {
        $value = PortalContent::cleanedOptionalField($path);

        if (! $value) {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        $relativePath = ltrim($value, '/');
        $absolutePath = public_path($relativePath);

        if (! is_file($absolutePath)) {
            return null;
        }

        $version = max((int) @filemtime($absolutePath), (int) @filectime($absolutePath));

        return asset($relativePath) . ($version > 0 ? '?v=' . $version : '');
    }
}
