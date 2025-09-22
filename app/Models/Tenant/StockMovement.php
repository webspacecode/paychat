<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = ['product_id','from_location_id','to_location_id','quantity','type','meta'];
    protected $casts = ['meta' => 'array'];
}
