<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ProductInventory extends Model
{
    protected $fillable = ['product_id', 'location_id', 'quantity'];
    public function product()  { return $this->belongsTo(Product::class); }
    public function location() { return $this->belongsTo(Location::class); }
}
