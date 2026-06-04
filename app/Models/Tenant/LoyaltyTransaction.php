<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class LoyaltyTransaction extends Model
{
    protected $table = 'loyalty_transactions';

    protected $fillable = [
        'customer_id',
        'order_id',
        'type',
        'points',
        'amount',
        'balance_after',
        'description',
        'meta',
        'created_by',
    ];

    protected $casts = [
        'points' => 'integer',
        'amount' => 'decimal:2',
        'balance_after' => 'integer',
        'meta' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
