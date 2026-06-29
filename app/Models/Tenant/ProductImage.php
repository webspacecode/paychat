<?php

namespace App\Models\Tenant;

class ProductImage extends BaseTenantModel
{
    protected $fillable = ['product_id', 'image_path'];
    public function product() { return $this->belongsTo(Product::class); }
    
    public function getUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }
}
