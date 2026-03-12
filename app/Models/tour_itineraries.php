<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tour_itineraries extends Model
{
    protected $table = 'tour_itineraries';
    protected $primaryKey = 'id';
    protected $fillable = [
        'tour_id',
        'day_no',
        'title',
        'content',
    ];
}
