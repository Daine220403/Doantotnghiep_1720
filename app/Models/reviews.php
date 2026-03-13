<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class reviews extends Model
{
    protected $table = 'reviews';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'tour_id',
        'rating',
        'comment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tour()
    {
        return $this->belongsTo(Tours::class, 'tour_id');
    }
    public function bookings()
    {
        return $this->belongsTo(Bookings::class, 'tour_id', 'tour_id');
    }
}
