<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\bookings;
use App\Models\tour_assignments;

class tour_departures extends Model
{
    protected $table = 'tour_departures';
    protected $primaryKey = 'id';
    protected $fillable = [
        'tour_id',
        'start_date',
        'end_date',
        'meeting_point',
        'capacity_total',
        'capacity_booked',
        'price_adult',
        'price_child',
        'price_infant',
        'price_youth',
        'single_room_surcharge',
        'status',
    ];

    public function tour()
    {
        return $this->belongsTo(Tours::class, 'tour_id');
    }

    public function bookings()
    {
        return $this->hasMany(bookings::class, 'departure_id');
    }

    public function assignment()
    {
        return $this->hasOne(tour_assignments::class, 'departure_id');
    }
}
