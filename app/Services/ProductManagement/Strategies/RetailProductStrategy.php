<?php

namespace App\Services\ProductManagement\Strategies;

class RetailProductStrategy implements ProductStrategyInterface
{
    public function createProduct(array $data) {
        // Simple product & stock record
    }

    public function updateProduct(int $id, array $data) {
        // Basic stock update
    }

    public function deleteProduct(int $id) {
        // Remove product
    }

    public function manageInventory(array $data) {
        // Stock In/Out
    }
}
