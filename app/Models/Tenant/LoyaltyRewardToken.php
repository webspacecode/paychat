<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class LoyaltyRewardToken extends Model
{
    protected $table = 'loyalty_reward_tokens';

    protected $fillable = [
        'customer_id',
        'token_hash',
        'expires_at',
        'revoked_at',
        'last_viewed_at',
        'meta',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'last_viewed_at' => 'datetime',
        'meta' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
