<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = ['product_id','location_id','description'];
    public function product() { return $this->belongsTo(Product::class); }
    public function items()   { return $this->hasMany(RecipeItem::class); }
}
