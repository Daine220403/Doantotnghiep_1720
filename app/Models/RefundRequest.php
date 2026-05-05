<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RefundRequest extends Model
{
    protected $table = 'refund_requests';
    protected $primaryKey = 'id';

    protected $fillable = [
        'refund_code',
        'booking_id',
        'order_id',
        'user_id',
        'refund_amount',
        'status',
        'refund_method',
        'vnpay_payment_code',
        'vnpay_transaction_no',
        'vnpay_bank_tran_no',
        'vnpay_response',
        'approved_by',
        'approved_at',
        'approval_note',
        'rejection_reason',
        'rejected_at',
        'refunded_at',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'refunded_at' => 'datetime',
        'vnpay_response' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->refund_code) {
                $model->refund_code = 'REF-' . strtoupper(Str::random(12));
            }
        });
    }

    // Relationships
    public function booking()
    {
        return $this->belongsTo(bookings::class, 'booking_id');
    }

    public function order()
    {
        return $this->belongsTo(orders::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function walletTransaction()
    {
        return $this->hasOne(RefundWalletTransaction::class, 'refund_request_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // Methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRefunded()
    {
        return $this->status === 'refunded';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function canApprove()
    {
        return $this->status === 'pending';
    }

    public function canReject()
    {
        return $this->status === 'pending';
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'refunded' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'failed' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusLabel()
    {
        return match($this->status) {
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'refunded' => 'Đã hoàn tiền',
            'rejected' => 'Đã từ chối',
            'failed' => 'Thất bại',
            default => 'Không xác định',
        };
    }
}
