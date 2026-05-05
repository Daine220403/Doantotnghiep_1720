<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundWalletTransaction extends Model
{
    protected $table = 'refund_wallet_transactions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'transaction_code',
        'user_id',
        'refund_wallet_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'status',
        'refund_request_id',
        'related_type',
        'related_id',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
    ];

    // Relationships
    public function wallet()
    {
        return $this->belongsTo(RefundWallet::class, 'refund_wallet_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function refundRequest()
    {
        return $this->belongsTo(RefundRequest::class, 'refund_request_id');
    }

    // Scopes
    public function scopeRefunds($query)
    {
        return $query->where('type', 'refund');
    }

    public function scopeWithdrawals($query)
    {
        return $query->where('type', 'withdrawal');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
