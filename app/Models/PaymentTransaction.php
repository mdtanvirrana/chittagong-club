<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'transaction_id',
        'member_id',
        'member_name',
        'amount',
        'currency',
        'status',
        'ssl_status',
        'session_key',
        'validation_id',
        'bank_transaction_id',
        'card_type',
        'store_amount',
        'note',
        'init_response',
        'callback_payload',
        'validation_response',
        'paid_at',
        'last_status_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'store_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'last_status_at' => 'datetime',
        ];
    }

    public function successfulTransaction()
    {
        return $this->hasOne(SuccessfulPaymentTransaction::class);
    }
}
