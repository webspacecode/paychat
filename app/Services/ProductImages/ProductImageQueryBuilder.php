<?php

namespace App\Services\ProductImages;

use App\Models\Tenant\Product;
use Illuminate\Support\Str;

class ProductImageQueryBuilder
{
    public function build(Product $product, $tenant = null): string
    {
        $product->loadMissing('categories:id,name,description');

        $parts = [
            $product->name,
            optional($product->categories->first())->description ?: optional($product->categories->first())->name,
            $product->type,
        ];

        $industry = strtolower((string) ($tenant->industry ?? ''));
        if (str_contains($industry, 'restaurant')) {
            $parts[] = 'restaurant food';
        } elseif (str_contains($industry, 'cafe')) {
            $parts[] = 'cafe food drink';
        } elseif (str_contains($industry, 'bakery')) {
            $parts[] = 'bakery food';
        } else {
            $parts[] = 'product';
        }

        return $this->normalize(implode(' ', array_filter($parts)));
    }

    private function normalize(string $query): string
    {
        $query = Str::of($query)
            ->lower()
            ->replaceMatches('/\bsku[-_\s]*[a-z0-9-]+\b/i', ' ')
            ->replaceMatches('/[^a-z0-9\s-]/i', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();

        return Str::limit($query, 120, '');
    }
}
