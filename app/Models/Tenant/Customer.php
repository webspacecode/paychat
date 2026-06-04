<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'pos_customers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'location_id',
        'customer_type',
        'loyalty_points',
        'total_visits',
        'total_spend',
        'last_visit_at',
        'meta',
    ];

    protected $casts = [
        'loyalty_points' => 'integer',
        'total_visits' => 'integer',
        'total_spend' => 'decimal:2',
        'last_visit_at' => 'datetime',
        'meta' => 'array',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }
}
