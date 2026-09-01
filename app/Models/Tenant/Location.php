<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'address',
        'type',
        'business_day_enabled',
        'business_day_start_time',
        'business_day_end_time',
        'timezone',
    ];

    protected $casts = [
        'business_day_enabled' => 'boolean',
    ];

    public function inventories() { return $this->hasMany(ProductInventory::class); }
}
