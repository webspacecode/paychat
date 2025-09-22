<?php 

namespace App\Services\ProductManagement\Strategies;

use App\Models\Tenant\Product;
use App\Models\Tenant\ProductInventory;
use App\Models\Tenant\StockMovement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\ProductManagement\Contracts\ProductStrategyInterface;

class DefaultProductStrategy implements ProductStrategyInterface
{
    public function create(array $data): Product
    {
        $payload = collect($data)->only(['name','sku','type','price','unit'])->all();
        $product = Product::create($payload);

        if (!empty($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $img) {
                if ($img instanceof \Illuminate\Http\UploadedFile) {
                    // store file into storage/app/public/products
                    $path = $img->store('products', 'public');
                    $product->images()->create(['image_path' => $path]);
                } elseif (is_string($img)) {
                    // fallback: accept string paths (maybe from API sync)
                    $product->images()->create(['image_path' => $img]);
                }
            }
        }
        return $product->load(['images','inventories','recipe.items']);
    }

    public function update(Product $product, array $data): Product
    {
        $payload = collect($data)->only(['name','type','price','unit'])->all();
        if (!empty($payload)) $product->update($payload);

        if (array_key_exists('images', $data)) {
            $product->images()->delete();
            if (!empty($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $img) {
                    if ($img instanceof \Illuminate\Http\UploadedFile) {
                        // store file into storage/app/public/products
                        $path = $img->store('products', 'public');
                        $product->images()->create(['image_path' => $path]);
                    } elseif (is_string($img)) {
                        // fallback: accept string paths (maybe from API sync)
                        $product->images()->create(['image_path' => $img]);
                    }
                }
            }
        }
        return $product->fresh()->load(['images','inventories','recipe.items']);
    }

    public function delete(Product $product): bool
    {
        return (bool) $product->delete();
    }

    public function search(string $keyword = null, ?string $type = null): Collection
    {
        $q = Product::query()->with('images');

        if ($keyword) {
            $q->where(fn($w) => $w->where('name','like',"%{$keyword}%")
                                  ->orWhere('sku','like',"%{$keyword}%"));
        }
        if ($type) $q->where('type',$type);

        return $q->orderBy('name')->limit(200)->get();
    }

    public function getById(int $id): ?Product
    {
        return Product::with(['images','inventories.location','recipe.items.rawProduct'])->find($id);
    }

    public function adjustInventory(Product $product, int $locationId, int $deltaQty, array $meta = []): ProductInventory
    {
        return DB::transaction(function () use ($product, $locationId, $deltaQty, $meta) {
            $inv = ProductInventory::lockForUpdate()->firstOrNew([
                'product_id' => $product->id,
                'location_id' => $locationId,
            ]);
            $newQty = (int)$inv->quantity + (int)$deltaQty;
            if ($newQty < 0) {
                throw ValidationException::withMessages(['quantity' => 'Insufficient stock at location']);
            }
            $inv->quantity = $newQty;
            $inv->save();

            StockMovement::create([
                'product_id' => $product->id,
                'from_location_id' => $deltaQty < 0 ? $locationId : null,
                'to_location_id'   => $deltaQty > 0 ? $locationId : null,
                'quantity' => abs($deltaQty),
                'type' => $deltaQty > 0 ? 'in' : 'out',
                'meta' => $meta,
            ]);

            return $inv;
        });
    }

    public function moveStock(Product $product, int $fromLocation, int $toLocation, int $quantity, array $meta = []): StockMovement
    {
        if ($quantity <= 0) throw ValidationException::withMessages(['quantity'=>'Quantity must be > 0']);
        if ($fromLocation === $toLocation) throw ValidationException::withMessages(['location'=>'Locations must differ']);

        return DB::transaction(function () use ($product,$fromLocation,$toLocation,$quantity,$meta) {
            $from = ProductInventory::lockForUpdate()->firstOrNew([
                'product_id'=>$product->id,'location_id'=>$fromLocation,
            ]);
            if ($from->quantity < $quantity) {
                throw ValidationException::withMessages(['quantity'=>'Not enough stock in source']);
            }
            $from->quantity -= $quantity; $from->save();

            $to = ProductInventory::lockForUpdate()->firstOrNew([
                'product_id'=>$product->id,'location_id'=>$toLocation,
            ]);
            $to->quantity += $quantity; $to->save();

            return StockMovement::create([
                'product_id'=>$product->id,
                'from_location_id'=>$fromLocation,
                'to_location_id'=>$toLocation,
                'quantity'=>$quantity,
                'type'=>'transfer',
                'meta'=>$meta,
            ]);
        });
    }
}
