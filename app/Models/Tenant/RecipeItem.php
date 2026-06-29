<?php

namespace App\Models\Tenant;

class RecipeItem extends BaseTenantModel
{
    protected $fillable = ['recipe_id','raw_product_id','quantity','unit'];
    public function recipe()     { return $this->belongsTo(Recipe::class); }
    public function rawProduct() { return $this->belongsTo(Product::class, 'raw_product_id'); }
}
