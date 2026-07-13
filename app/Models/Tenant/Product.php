<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'type',
        'price',
        'unit',
        'track_inventory',
        'low_stock_threshold',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'track_inventory' => 'boolean',
        'low_stock_threshold' => 'integer',
        'is_active' => 'boolean',
    ];

    public function images()      { return $this->hasMany(ProductImage::class); }
    public function inventories() { return $this->hasMany(ProductInventory::class); }
    public function recipe()      { return $this->hasOne(Recipe::class); }
    public function program()     { return $this->hasOne(\App\Models\Tenant\Registration\Program::class); }

    public function scopeType($q, string $type) { return $q->where('type', $type); }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
