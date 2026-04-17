<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $table = 'contact_messages';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'preferred_contact',
        'status',
        'ip_address', // Lưu địa chỉ IP của người gửi
        'user_agent', // Lưu thông tin trình duyệt của người gửi
        'handled_at',
        'notes',
    ];

    protected $casts = [
        'handled_at' => 'datetime',
    ];
}
