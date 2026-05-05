<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundWallet extends Model
{
    protected $table = 'refund_wallets';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'balance',
        'total_received',
        'total_withdrawn',
        'status',
        'last_updated_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_received' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'last_updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany(RefundWalletTransaction::class, 'refund_wallet_id')
            ->latest();
    }

    public function refundRequests()
    {
        return $this->user->refundRequests();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeLocked($query)
    {
        return $query->where('status', 'locked');
    }

    // Methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isLocked()
    {
        return $this->status === 'locked';
    }

    /**
     * Thêm tiền vào ví
     */
    public function addBalance($amount, $type = 'refund', $description = null, $relatedId = null, $relatedType = null)
    {
        $this->balance += $amount;
        $this->total_received += $amount;
        $this->last_updated_at = now();
        $this->save();

        // Tạo transaction record
        RefundWalletTransaction::create([
            'transaction_code' => 'TXN-' . strtoupper(\Illuminate\Support\Str::random(12)),
            'user_id' => $this->user_id,
            'refund_wallet_id' => $this->id,
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $this->balance - $amount,
            'balance_after' => $this->balance,
            'status' => 'completed',
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'description' => $description,
        ]);

        return $this;
    }

    /**
     * Trừ tiền từ ví
     */
    public function deductBalance($amount, $type = 'withdrawal', $description = null, $relatedId = null, $relatedType = null)
    {
        if ($this->balance < $amount) {
            throw new \Exception('Số dư không đủ để thực hiện giao dịch này');
        }

        $this->balance -= $amount;
        $this->total_withdrawn += $amount;
        $this->last_updated_at = now();
        $this->save();

        // Tạo transaction record
        RefundWalletTransaction::create([
            'transaction_code' => 'TXN-' . strtoupper(\Illuminate\Support\Str::random(12)),
            'user_id' => $this->user_id,
            'refund_wallet_id' => $this->id,
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $this->balance + $amount,
            'balance_after' => $this->balance,
            'status' => 'completed',
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'description' => $description,
        ]);

        return $this;
    }

    /**
     * Khóa ví
     */
    public function lock($reason = null)
    {
        $this->status = 'locked';
        $this->save();
        return $this;
    }

    /**
     * Mở khóa ví
     */
    public function unlock()
    {
        $this->status = 'active';
        $this->save();
        return $this;
    }
}
