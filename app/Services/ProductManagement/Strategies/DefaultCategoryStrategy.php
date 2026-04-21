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

    public function search(?string $keyword = null, ?int $locationId = null): Collection
    {
        return Category::query()

            // 🔍 Keyword filter
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
                });
            })

            // 🎯 Only categories having SELLABLE products
            ->whereHas('products', function ($p) use ($locationId) {

                $p->where(function ($w) use ($locationId) {

                    // ✅ Recipe products (always allowed)
                    $w->where('type', 'recipe');

                    // ✅ Simple products (check stock if location provided)
                    $w->orWhere(function ($q2) use ($locationId) {

                        $q2->where('type', 'simple');

                        if ($locationId) {
                            $q2->whereHas('inventories', function ($inv) use ($locationId) {
                                $inv->where('location_id', $locationId)
                                    ->where('quantity', '>', 0);
                            });
                        }
                    });

                    // ❌ Raw products excluded automatically
                });
            })

            ->orderBy('name')
            ->get();
    }

    public function getById(int $id): ?Category
    {
        return Category::find($id);
    }
}
