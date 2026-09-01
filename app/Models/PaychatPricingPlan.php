<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaychatPricingPlan extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'monthly_price',
        'yearly_price',
        'currency',
        'trial_days',
        'is_trial',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'trial_days' => 'integer',
        'is_trial' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(PaychatFeature::class, 'paychat_feature_plan')
            ->withPivot('limits')
            ->withTimestamps();
    }
}
