<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BakeryOrderItem extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $fillable = [
        'bakery_order_id',
        'product_id',
        'product_name',
        'sku',
        'quantity',
        'unit_price',
        'line_total',
        'meta',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'meta' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(BakeryOrder::class, 'bakery_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
