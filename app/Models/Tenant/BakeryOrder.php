<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BakeryOrder extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $fillable = [
        'bakery_order_no',
        'location_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'fulfillment_type',
        'fulfillment_at',
        'delivery_address',
        'status',
        'payment_status',
        'flavour',
        'weight_value',
        'weight_unit',
        'cake_message',
        'design_notes',
        'reference_image_path',
        'subtotal',
        'discount',
        'tax',
        'shipping',
        'total',
        'paid_amount',
        'balance_due',
        'notes',
        'meta',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fulfillment_at' => 'datetime',
        'weight_value' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'meta' => 'array',
    ];

    public function payments()
    {
        return $this->hasMany(BakeryOrderPayment::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
