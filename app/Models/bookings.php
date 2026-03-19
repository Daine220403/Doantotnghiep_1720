<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class bookings extends Model
{
    protected $table = 'bookings';
    protected $primaryKey = 'id';
    protected $fillable = [
        'order_id',
        'departure_id',
        'note',
        'status'
    ];

    public function departure()
    {
        return $this->belongsTo(tour_departures::class, 'departure_id');
    }

    public function order()
    {
        return $this->belongsTo(orders::class, 'order_id');
    }

    public function passengers()
    {
        return $this->hasMany(booking_passengers::class, 'booking_id');
    }
}
