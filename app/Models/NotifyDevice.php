<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotifyDevice extends Model
{
    protected $table = 'T_Notify_Device';

    protected $primaryKey = 'id_notify_device_key';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
        'dtt_added' => 'datetime',
        'dtt_mod' => 'datetime',
    ];
}
