<?php

namespace App\Models\Tenant;

class Recipe extends BaseTenantModel
{
    protected $fillable = ['product_id','location_id','description'];
    public function product() { return $this->belongsTo(Product::class); }
    public function items()   { return $this->hasMany(RecipeItem::class); }
}
