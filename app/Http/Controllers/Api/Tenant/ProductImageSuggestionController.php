<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ExternalProductImageSuggestion;
use App\Models\Tenant\Product;
use App\Services\ProductImages\ProductImageDiscoveryService;
use Illuminate\Http\Request;

class ProductImageSuggestionController extends Controller
{
    public function store(Request $request, ProductImageDiscoveryService $service, string $tenantSlug, $product)
    {
        $validated = $request->validate([
            'refresh' => ['nullable', 'boolean'],
            'force' => ['nullable', 'boolean'],
        ]);

        return response()->json(
            $service->suggest($this->product($product), app('currentTenant'), $validated)
        );
    }

    public function accept(
        ProductImageDiscoveryService $service,
        string $tenantSlug,
        $product,
        $suggestion
    ) {
        $fresh = $service->accept($this->product($product), $this->suggestion($suggestion), app('currentTenant'));

        return response()->json([
            'success' => true,
            'product' => $fresh,
        ]);
    }

    public function reject(
        ProductImageDiscoveryService $service,
        string $tenantSlug,
        $product,
        $suggestion
    ) {
        return response()->json([
            'success' => true,
            'suggestion' => $service->reject($this->product($product), $this->suggestion($suggestion)),
        ]);
    }

    private function product($product): Product
    {
        if ($product instanceof Product) {
            return $product;
        }

        abort_unless(is_numeric($product), 404, 'Product not found');

        return Product::query()->whereKey((int) $product)->firstOrFail();
    }

    private function suggestion($suggestion): ExternalProductImageSuggestion
    {
        if ($suggestion instanceof ExternalProductImageSuggestion) {
            return $suggestion;
        }

        abort_unless(is_numeric($suggestion), 404, 'Image suggestion not found');

        return ExternalProductImageSuggestion::query()->whereKey((int) $suggestion)->firstOrFail();
    }
}
