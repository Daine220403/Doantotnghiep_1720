<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\tour_departures;
use App\Models\partner_services;

class departure_services extends Model
{
    protected $table = 'departure_services';
    protected $primaryKey = 'id';

    protected $fillable = [
        'departure_id',
        'partner_service_id',
        'service_date',
        'qty',
        'unit_price',
        'total_price',
        'status',
        'note',
        'confirmed_at',
    ];

    public function departure()
    {
        return $this->belongsTo(tour_departures::class, 'departure_id');
    }

    public function partnerService()
    {
        return $this->belongsTo(partner_services::class, 'partner_service_id');
    }
}

