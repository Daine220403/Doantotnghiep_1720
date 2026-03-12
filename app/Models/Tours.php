<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\tour_images;

class Tours extends Model
{
    protected $table = 'tours';
    protected $primaryKey = 'id';
    protected $fillable = [
        'code',
        'title',
        'slug',
        'tour_type',
        'summary',
        'description',
        'duration_days',
        'duration_nights',
        'departure_location',
        'destination_text',
        'base_price_from',
        'status',
    ];
    public function images()
    {
        return $this->hasMany(tour_images::class, 'tour_id');
    }
    public function itineraries()
    {
        return $this->hasMany(tour_itineraries::class, 'tour_id');
    }

    public function departures()
    {
        return $this->hasMany(tour_departures::class, 'tour_id');
    }

    public function policies()
    {
        return $this->hasMany(tour_policies::class, 'tour_id');
    }
}
