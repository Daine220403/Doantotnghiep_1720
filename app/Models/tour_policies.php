<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tour_policies extends Model
{
    protected $table = 'tour_policies';
    protected $primaryKey = 'id';
    protected $fillable = [
        'tour_id',
        'type',
        'content',
        'sort_order',
    ];
}
