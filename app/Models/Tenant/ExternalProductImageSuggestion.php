<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ExternalProductImageSuggestion extends Model
{
    protected $fillable = [
        'product_id',
        'provider',
        'query',
        'provider_image_id',
        'preview_url',
        'full_url',
        'photographer_name',
        'photographer_url',
        'license',
        'status',
        'error_message',
        'searched_at',
        'accepted_at',
        'meta',
    ];

    protected $casts = [
        'searched_at' => 'datetime',
        'accepted_at' => 'datetime',
        'meta' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
