<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{
    protected $appends = [
        'resolved_image_url',
        'resolved_image_source',
    ];

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

    public function getResolvedImageUrlAttribute(): ?string
    {
        $image = $this->resolvedImage();

        return $image?->url;
    }

    public function getResolvedImageSourceAttribute(): ?string
    {
        $image = $this->resolvedImage();

        return $image?->source;
    }

    private function resolvedImage(): ?ProductImage
    {
        if (! Schema::hasTable('product_images')) {
            return null;
        }

        $images = $this->relationLoaded('images')
            ? $this->images
            : $this->images()->get();

        $preferredLocalSources = [null, '', 'merchant_upload', 'bulk_upload', 'imported_path'];

        return $images->first(fn ($image) => in_array($image->source, $preferredLocalSources, true) && $image->hasRenderableImage())
            ?: $images->first(fn ($image) => $image->source === 'external_approved' && $image->hasRenderableImage())
            ?: $images->first(fn ($image) => $image->hasRenderableImage())
            ?: $images->first();
    }
}
