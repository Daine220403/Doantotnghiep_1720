<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class booking_passengers extends Model
{
    protected $table = 'booking_passengers';
    protected $primaryKey = 'id';
    protected $fillable = [
        'booking_id',
        'full_name',
        'gender',
        'dob',
        'id_no',
        'passenger_type',
        'single_room',
        'single_room_surcharge',
    ];
}
