<?php 

namespace App\Services\ProductManagement\Strategies;

use App\Models\Tenant\Category;
use Illuminate\Database\Eloquent\Collection;
use App\Services\ProductManagement\Contracts\CategoryStrategyInterface;


class DefaultCategoryStrategy implements CategoryStrategyInterface
{
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        return $category;
    }

    public function delete(Category $category): bool
    {
        return $category->delete();
    }

    public function search(string $keyword): Collection
    {
        return Category::where('name', 'like', "%{$keyword}%")
            ->orWhere('description', 'like', "%{$keyword}%")
            ->get();
    }

    public function getById(int $id): ?Category
    {
        return Category::find($id);
    }
}
