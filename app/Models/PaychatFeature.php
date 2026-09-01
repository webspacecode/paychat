<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaychatFeature extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'category',
        'source',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(PaychatPricingPlan::class, 'paychat_feature_plan')
            ->withPivot('limits')
            ->withTimestamps();
    }
}
