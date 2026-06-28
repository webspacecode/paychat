<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Tenant\Product;
use App\Models\Tenant\StockMovement;

class InventoryService
{
    public function getInventory(Request $request)
    {
        $locationId = $request->get('location_id');
        $from = $request->get('from') ?? $request->get('date_from');
        $to = $request->get('to') ?? $request->get('date_to');
        $productId = $request->get('product_id');
        $type = $request->get('type');

        $productsQuery = Product::query()->with(['inventories', 'categories:id,name,description']);

        if ($type) {
            $productsQuery->where('type', $type);
        }

        if ($productId) {
            $productsQuery->where('id', $productId);
        }

        $products = $productsQuery->orderBy('name')->get();
        $productIds = $products->pluck('id');

        $movements = StockMovement::query()
            ->whereIn('product_id', $productIds)
            ->when($locationId, function ($query) use ($locationId) {
                $query->where(function ($locations) use ($locationId) {
                    $locations->where('from_location_id', $locationId)
                        ->orWhere('to_location_id', $locationId);
                });
            })
            ->when($from && $to, fn ($query) => $query->whereBetween('created_at', [$from, $to]))
            ->latest()
            ->get()
            ->groupBy('product_id');

        $result = [];

        foreach ($products as $product) {
            $currentStock = $locationId
                ? (int) optional($product->inventories->firstWhere('location_id', (int) $locationId))->quantity
                : (int) $product->inventories->sum('quantity');

            $productMovements = $movements->get($product->id, collect());
            $totalIn = $productMovements
                ->filter(fn ($movement) => $movement->type === 'in' && (! $locationId || (int) $movement->to_location_id === (int) $locationId))
                ->sum('quantity');
            $totalOut = $productMovements
                ->filter(fn ($movement) => $movement->type === 'out' && (! $locationId || (int) $movement->from_location_id === (int) $locationId))
                ->sum('quantity');
            [$status, $statusLabel] = $this->stockStatus($product, $currentStock);

            $result[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'type' => $product->type,
                'unit' => $product->unit,
                'track_inventory' => (bool) $product->track_inventory,
                'low_stock_threshold' => $product->low_stock_threshold,
                'is_active' => (bool) $product->is_active,
                'current_stock' => $currentStock,
                'stock_status' => $status,
                'stock_status_label' => $statusLabel,
                'total_in' => $totalIn,
                'total_out' => $totalOut,
                'movements' => $productMovements->take(5)->values(),
            ];
        }

        return $result;
    }

    private function stockStatus(Product $product, int $currentStock): array
    {
        if (! $product->track_inventory || $product->type === 'recipe') {
            return ['not_tracked', 'Not Tracked'];
        }

        if ($currentStock <= 0) {
            return ['out_of_stock', 'Out of Stock'];
        }

        if ($product->low_stock_threshold !== null && $currentStock <= (int) $product->low_stock_threshold) {
            return ['low_stock', 'Low Stock'];
        }

        return ['ok', 'OK'];
    }
}
