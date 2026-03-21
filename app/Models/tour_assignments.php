<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tour_assignments extends Model
{
    protected $table = 'tour_assignments';

    protected $fillable = [
        'departure_id',
        'guide_id',
        'assigned_by',
        'status',
    ];

    public function departure()
    {
        return $this->belongsTo(tour_departures::class, 'departure_id');
    }

    public function guide()
    {
        return $this->belongsTo(User::class, 'guide_id');
    }
}
