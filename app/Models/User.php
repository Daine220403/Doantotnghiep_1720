<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\tour_assignments;
use App\Models\partners;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'department_id',
        'partner_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function guideAssignments()
    {
        return $this->hasMany(tour_assignments::class, 'guide_id');
    }

    public function partner()
    {
        return $this->belongsTo(partners::class, 'partner_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function refundWallet()
    {
        return $this->hasOne(RefundWallet::class, 'user_id');
    }

    public function refundRequests()
    {
        return $this->hasMany(RefundRequest::class, 'user_id');
    }

    public function approvedRefundRequests()
    {
        return $this->hasMany(RefundRequest::class, 'approved_by');
    }

    public function walletTransactions()
    {
        return $this->hasMany(RefundWalletTransaction::class, 'user_id');
    }

    /**
     * Lấy hoặc tạo ví hoàn tiền cho người dùng
     */
    public function getOrCreateRefundWallet()
    {
        return $this->refundWallet()->firstOrCreate(
            ['user_id' => $this->id],
            [
                'balance' => 0,
                'total_received' => 0,
                'total_withdrawn' => 0,
                'status' => 'active',
            ]
        );
    }
}
