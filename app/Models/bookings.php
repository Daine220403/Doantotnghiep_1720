<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class bookings extends Model
{
    protected $table = 'bookings';
    protected $primaryKey = 'id';
    protected $fillable = [
        'tour_id',
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
}
