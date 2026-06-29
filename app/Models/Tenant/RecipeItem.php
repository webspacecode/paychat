<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class RecipeItem extends Model
{
    protected $fillable = ['recipe_id','raw_product_id','quantity','unit'];
    public function recipe()     { return $this->belongsTo(Recipe::class); }
    public function rawProduct() { return $this->belongsTo(Product::class, 'raw_product_id'); }
}
