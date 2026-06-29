<?php

namespace App\Models\Tenant;

class StockMovement extends BaseTenantModel
{
    protected $table = 'stock_movements';

    protected $fillable = ['product_id','from_location_id','to_location_id','order_id','quantity','type','meta'];
    protected $casts = ['meta' => 'array'];
}
