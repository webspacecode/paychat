<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BakeryOrderPayment extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $fillable = [
        'bakery_order_id',
        'payment_method',
        'amount',
        'status',
        'transaction_id',
        'provider',
        'provider_ref',
        'paid_at',
        'received_by',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'meta' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(BakeryOrder::class, 'bakery_order_id');
    }
}
