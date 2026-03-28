<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\partner_services;

class partners extends Model
{
    protected $table = 'partners';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'type',
        'phone',
        'email',
        'address',
        'status',
    ];

    public function services()
    {
        return $this->hasMany(partner_services::class, 'partner_id');
    }
}
