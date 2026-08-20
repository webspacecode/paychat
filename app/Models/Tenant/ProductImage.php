<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    private static array $publicDiskExistsCache = [];

    protected $fillable = [
        'product_id',
        'image_path',
        'source',
        'provider',
        'provider_image_id',
        'provider_url',
        'author_name',
        'author_url',
        'license',
        'meta',
        'is_primary',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_primary' => 'boolean',
    ];

    public function product() { return $this->belongsTo(Product::class); }
    
    public function getUrlAttribute()
    {
        if ($this->image_path && preg_match('/^(https?:|data:|blob:)/i', $this->image_path)) {
            return $this->image_path;
        }

        if (! $this->image_path) {
            return null;
        }

        return asset('storage/' . $this->publicStoragePath());
    }

    public function publicStoragePath(): string
    {
        $path = ltrim(str_replace(['\\', '"'], '', (string) $this->image_path), '/');

        if (str_starts_with($path, 'tenants/')) {
            return $path;
        }

        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;
        $tenantPath = $tenant?->id ? "tenants/{$tenant->id}/{$path}" : null;

        if ($tenantPath && Storage::disk('public')->exists($tenantPath)) {
            return $tenantPath;
        }

        return $path;
    }

    public function hasRenderableImage(): bool
    {
        if (! $this->image_path) {
            return false;
        }

        if (preg_match('/^(https?:|data:|blob:)/i', $this->image_path)) {
            return true;
        }

        $path = $this->publicStoragePath();

        if (! array_key_exists($path, self::$publicDiskExistsCache)) {
            self::$publicDiskExistsCache[$path] = Storage::disk('public')->exists($path);
        }

        return self::$publicDiskExistsCache[$path];
    }
}
