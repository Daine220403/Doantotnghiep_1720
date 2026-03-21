<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class payments extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'id';

    protected $fillable = [
        'order_id',
        'payment_code',
        'payment_type',
        'method',
        'amount',
        'status',
        'paid_at',
        'transaction_ref',
        'raw_response',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'raw_response' => 'array',
    ];
}
