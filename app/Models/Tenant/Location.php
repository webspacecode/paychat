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
        'service_charge_enabled',
        'service_charge_type',
        'service_charge_value',
        'service_charge_default_apply',
    ];

    protected $casts = [
        'business_day_enabled' => 'boolean',
        'service_charge_enabled' => 'boolean',
        'service_charge_default_apply' => 'boolean',
        'service_charge_value' => 'decimal:2',
    ];

    public function inventories() { return $this->hasMany(ProductInventory::class); }
}
