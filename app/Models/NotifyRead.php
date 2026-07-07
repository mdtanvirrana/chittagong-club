<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotifyRead extends Model
{
    protected $table = 'T_Notify_Read';

    protected $primaryKey = 'id_notify_read_key';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'read_at' => 'datetime',
        'dtt_added' => 'datetime',
        'dtt_mod' => 'datetime',
    ];
}
