<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'sku', 'type', 'price', 'unit'];

    public function images()      { return $this->hasMany(ProductImage::class); }
    public function inventories() { return $this->hasMany(ProductInventory::class); }
    public function recipe()      { return $this->hasOne(Recipe::class); }

    public function scopeType($q, string $type) { return $q->where('type', $type); }
}
