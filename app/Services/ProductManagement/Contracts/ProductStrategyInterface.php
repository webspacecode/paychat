<?php

namespace App\Services\ProductManagement\Contracts;

use Illuminate\Http\Request;
use App\Models\Tenant\Product;
use App\Models\Tenant\ProductInventory;
use App\Models\Tenant\StockMovement;
use Illuminate\Database\Eloquent\Collection;

interface ProductStrategyInterface
{
    // CRUD
    public function create(array $data);
    public function getProductPayload(array $data);
    public function update(Product $product, array $data);
    public function delete(Product $product);

    // Query
    public function search(?string $keyword = null, ?string $type = null, ?int $locationId = null);
    public function getById(int $id);

    // Inventory
    public function adjustInventory(Product $product, int $locationId, int $deltaQty, array $meta = []);
    public function moveStock(Product $product, int $fromLocation, int $toLocation, int $quantity, array $meta = []);

}
