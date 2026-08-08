<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PosFavoriteProduct;
use App\Models\Tenant\Product;
use Illuminate\Http\Request;

class PosFavoriteProductController extends Controller
{
    public function index()
    {
        $favorites = PosFavoriteProduct::query()
            ->with(['product.images', 'product.categories:id,name,description', 'product.inventories'])
            ->whereHas('product', fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $favorites,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        Product::query()
            ->whereKey($validated['product_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $nextSortOrder = (int) PosFavoriteProduct::max('sort_order') + 1;

        $favorite = PosFavoriteProduct::firstOrCreate(
            ['product_id' => $validated['product_id']],
            ['sort_order' => $nextSortOrder]
        );

        return response()->json([
            'data' => $favorite->load(['product.images', 'product.categories:id,name,description', 'product.inventories']),
        ], $favorite->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy($tenantSlug, int $product)
    {
        PosFavoriteProduct::where('product_id', $product)->delete();

        return response()->json([
            'message' => 'Favorite removed',
        ]);
    }
}
