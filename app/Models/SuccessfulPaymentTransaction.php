<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuccessfulPaymentTransaction extends Model
{
    protected $fillable = [
        'payment_transaction_id',
        'transaction_id',
        'member_id',
        'member_name',
        'amount',
        'currency',
        'validation_id',
        'bank_transaction_id',
        'card_type',
        'store_amount',
        'note',
        'validation_response',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'store_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function paymentTransaction()
    {
        return $this->belongsTo(PaymentTransaction::class);
    }
}
