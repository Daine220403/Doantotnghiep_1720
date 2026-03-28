<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\partners;
use App\Models\departure_services;

class partner_services extends Model
{
    protected $table = 'partner_services';
    protected $primaryKey = 'id';

    protected $fillable = [
        'partner_id',
        'name',
        'service_type',
        'description',
        'status',
    ];

    public function partner()
    {
        return $this->belongsTo(partners::class, 'partner_id');
    }

    public function departureServices()
    {
        return $this->hasMany(departure_services::class, 'partner_service_id');
    }
}
