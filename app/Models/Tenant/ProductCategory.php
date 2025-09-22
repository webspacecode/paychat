<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = ['name'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }
}
