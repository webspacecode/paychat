<?php

namespace App\Models\Tenant;

class Location extends BaseTenantModel
{
    protected $fillable = ['name', 'address', 'type'];
    public function inventories() { return $this->hasMany(ProductInventory::class); }
}
