<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Category;
use App\Services\ProductManagement\Strategies\CategoryStrategyResolver;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $resolver;

    public function __construct(CategoryStrategyResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'industry' => 'nullable|string', // tells which strategy to use
        ]);

        $industry = $validated['industry'] ?? 'default';

        $strategy = $this->resolver->resolve($industry);
        $category = $strategy->create($validated);

        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            'industry' => 'nullable|string',
        ]);

        $industry = $validated['industry'] ?? 'default';

        $strategy = $this->resolver->resolve($industry);
        $category = $strategy->update($category, $validated);

        return response()->json($category);
    }

    public function destroy(Category $category, Request $request)
    {
        $industry = $request->get('industry', 'default');

        $strategy = $this->resolver->resolve($industry);
        $strategy->delete($category);

        return response()->json(['message' => 'Category deleted successfully']);
    }

    public function search(Request $request)
    {
        $request->validate([
            'keyword' => 'nullable|string',
            'industry' => 'nullable|string',
        ]);

        $industry = $request->get('industry', 'default');
        $strategy = $this->resolver->resolve($industry);

        $categories = $strategy->search($request->keyword);

        return response()->json($categories);
    }

    public function show(Request $request, $id)
    {
        $industry = $request->get('industry', 'default');
        $strategy = $this->resolver->resolve($industry);

        $category = $strategy->getById($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category);
    }
}
