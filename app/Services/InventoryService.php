<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Tenant\Product;
use App\Models\Tenant\ProductInventory;
use App\Models\Tenant\StockMovement;

class InventoryService
{
    public function getInventory(Request $request)
    {
        $locationId = $request->get('location_id');
        $from = $request->get('from');
        $to = $request->get('to');
        $productId = $request->get('product_id');
        $type = $request->get('type');

        // 🔹 PRODUCTS QUERY
        $productsQuery = Product::query();

        if ($type) {
            $productsQuery->where('type', $type);
        }

        if ($productId) {
            $productsQuery->where('id', $productId);
        }

        $products = $productsQuery->get();

        $result = [];

        foreach ($products as $product) {

            // 🔹 CURRENT STOCK
            $inventory = ProductInventory::where('product_id', $product->id)
                ->when($locationId, fn($q) => $q->where('location_id', $locationId))
                ->first();

            $currentStock = $inventory->quantity ?? 0;

            // 🔹 STOCK MOVEMENTS
            $movementsQuery = StockMovement::where('product_id', $product->id)
                ->when($locationId, fn($q) => $q->where('from_location_id', $locationId));

            if ($from && $to) {
                $movementsQuery->whereBetween('created_at', [$from, $to]);
            }

            $movements = $movementsQuery->get();

            $totalIn = $movements->where('type', 'in')->sum('quantity');
            $totalOut = $movements->where('type', 'out')->sum('quantity');

            $result[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'type' => $product->type,
                'unit' => $product->unit,
                'current_stock' => $currentStock,
                'total_in' => $totalIn,
                'total_out' => $totalOut,
                'movements' => $movements
            ];
        }

        return $result;
    }
}