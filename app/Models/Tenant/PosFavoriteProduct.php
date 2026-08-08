<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class PosFavoriteProduct extends Model
{
    protected $fillable = [
        'product_id',
        'sort_order',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
