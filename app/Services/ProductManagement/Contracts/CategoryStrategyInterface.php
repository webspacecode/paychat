<?php

namespace App\Services\ProductManagement\Contracts;

use App\Models\Tenant\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryStrategyInterface
{
    public function create(array $data): Category;
    public function update(Category $category, array $data): Category;
    public function delete(Category $category): bool;

    public function search(string $keyword): Collection;
    public function getById(int $id): ?Category;
}
