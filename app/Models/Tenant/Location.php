<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['name', 'address'];
    public function inventories() { return $this->hasMany(ProductInventory::class); }
}
