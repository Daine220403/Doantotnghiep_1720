<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tour_images extends Model
{
    protected $table = 'tour_images';
    protected $primaryKey = 'id';
    protected $fillable = [
        'tour_id',
        'url',
        'sort_order',
    ];
}
