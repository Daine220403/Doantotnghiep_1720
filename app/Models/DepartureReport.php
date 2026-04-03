<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartureReport extends Model
{
    protected $table = 'departure_reports';

    protected $fillable = [
        'departure_id',
        'guide_id',
        'summary',
        'general_evaluation',
        'incidents',
        'itinerary_notes',
        'extra_cost_total',
        'customer_feedback',
        'guide_suggestion',
        'status',
        'manager_note',
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
