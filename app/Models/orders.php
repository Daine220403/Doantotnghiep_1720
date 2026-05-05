<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class orders extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $fillable = [
        'order_code',
        'user_id',
        'contact_name',
        'contact_phone',
        'contact_email',
        'subtotal',
        'discount_total',
        'total_amount',
        'status',
    ];
    public function orderDetails()
    {
        return $this->hasMany(order_details::class, 'order_id');
    }

    public function payments()
    {
        return $this->hasMany(payments::class, 'order_id');
    }

    public function refundRequests()
    {
        return $this->hasMany(RefundRequest::class, 'order_id');
    }

    public function latestRefundRequest()
    {
        return $this->hasOne(RefundRequest::class, 'order_id')->latest();
    }
}
