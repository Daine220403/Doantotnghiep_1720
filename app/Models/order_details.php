<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class order_details extends Model
{
    protected $table = 'order_details';
    protected $primaryKey = 'id';
    protected $fillable = [
        'order_id',
        'item_type',
        'item_id',
        'item_name',
        'qty',
        'unit_price',
        'line_total',
        'meta'
    ];
        
}
