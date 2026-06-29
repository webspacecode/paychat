<?php

namespace App\Models\Tenant;

class ProductInventory extends BaseTenantModel
{
    protected $fillable = ['product_id', 'location_id', 'quantity'];
    public function product()  { return $this->belongsTo(Product::class); }
    public function location() { return $this->belongsTo(Location::class); }
}
