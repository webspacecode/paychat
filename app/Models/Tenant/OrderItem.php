<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'pos_order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'discount',
        'tax',
        'total',
        'kitchen_status',
        'kitchen_batch_id',
        'sent_to_kitchen_at',
        'item_status',
    ];

    protected $casts = [
        'sent_to_kitchen_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function kitchenBatch()
    {
        return $this->belongsTo(KitchenBatch::class);
    }
}
