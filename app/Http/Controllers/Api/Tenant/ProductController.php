<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\ProductManagement\Strategies\ProductStrategyResolver;

class ProductController extends Controller
{
    public function __construct(private ProductStrategyResolver $resolver) {
        
    }

    // CREATE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'industry' => ['required', Rule::in(['restaurant','bakery','cafe','retail'])],
            'name'  => ['required','string','max:255'],
            'sku'   => ['required','string','max:255','unique:products,sku'],
            'type'  => ['required', Rule::in(['basic','raw','semi_finished','finished','recipe','other'])],
            'price' => ['nullable','numeric','min:0'],
            'unit'  => ['nullable','string','max:50'],
            'images'=> ['sometimes','array'],
            'images.*' => ['string'],
            // recipe fields:
            'location_id' => ['nullable','integer','exists:locations,id'],
            'description' => ['nullable','string'],
            'items'       => ['sometimes','array'],
            'items.*.raw_product_id' => ['required_with:items','integer','exists:products,id'],
            'items.*.quantity'       => ['required_with:items','integer','min:1'],
            'items.*.unit'           => ['nullable','string','max:50'],
        ]);

        $industryStrategy = $this->resolver::resolve($validated['industry']); // 👈 resolve by industry
        $product = $industryStrategy->create($validated);

        return response()->json($product, 201);
    }

    // READ (list/search with filters)
    public function index(Request $request)
    {
        $validated = $request->validate([
            'industry' => ['required', Rule::in(['restaurant','bakery','cafe','retail'])], // 👈 new
            'keyword'  => ['nullable','string'],
            'type'     => ['nullable', Rule::in(['basic','raw','semi_finished','finished','recipe','other'])],
        ]);

        $industryStrategy = $this->resolver::resolve($validated['industry']); // 👈 resolve by industry
        $items = $industryStrategy->search($validated['keyword'] ?? null, $validated['type'] ?? null);

        return response()->json($items);
    }

    // SHOW
    public function show(Request $request, int $id)
    {
        $validated = $request->validate([
            'industry' => ['required', Rule::in(['restaurant','bakery','cafe','retail'])], // 👈 new
        ]);

        $industryStrategy = $this->resolver::resolve($validated['industry']); // 👈 resolve by industry
        $product  = $industryStrategy->getById($id);

        return $product
            ? response()->json($product)
            : response()->json(['message' => 'Product not found'], 404);
    }

    // UPDATE
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'industry' => ['required', Rule::in(['restaurant','bakery','cafe','retail'])], // 👈 new
            'name'  => ['sometimes','string','max:255'],
            'type'  => ['sometimes', Rule::in(['basic','raw','semi_finished','finished','recipe','other'])],
            'price' => ['nullable','numeric','min:0'],
            'unit'  => ['nullable','string','max:50'],
            'images'=> ['sometimes','array'],
            'images.*' => ['string'],

            // recipe:
            'location_id' => ['nullable','integer','exists:locations,id'],
            'description' => ['nullable','string'],
            'items'       => ['sometimes','array'],
            'items.*.raw_product_id' => ['required_with:items','integer','exists:products,id'],
            'items.*.quantity'       => ['required_with:items','integer','min:1'],
            'items.*.unit'           => ['nullable','string','max:50'],
        ]);

        $industryStrategy = $this->resolver::resolve($validated['industry']); // 👈 resolve by industry
        $updated = $industryStrategy->update($product, $validated);

        return response()->json($updated);
    }

    // DELETE
    public function destroy(Request $request, Product $product)
    {
        $validated = $request->validate([
            'industry' => ['required', Rule::in(['restaurant','bakery','cafe','retail'])], // 👈 new
        ]);

        $industryStrategy = $this->resolver::resolve($validated['industry']); // 👈 resolve by industry
        $industryStrategy->delete($product);

        return response()->json(['message' => 'Product deleted successfully']);
    }

    // INVENTORY: adjust (+/-)
    public function adjustInventory(Request $request, Product $product)
    {
        $validated = $request->validate([
            'industry'    => ['required', Rule::in(['restaurant','bakery','cafe','retail'])], // 👈 new
            'location_id' => ['required','integer','exists:locations,id'],
            'delta_qty'   => ['required','integer'],
            'meta'        => ['nullable','array'],
        ]);

        $industryStrategy = $this->resolver::resolve($validated['industry']); // 👈 resolve by industry
        $inventory = $industryStrategy->adjustInventory($product, (int)$validated['location_id'], (int)$validated['delta_qty'], $validated['meta'] ?? []);

        return response()->json($inventory);
    }

    // INVENTORY: transfer
    public function moveStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'industry'        => ['required', Rule::in(['restaurant','bakery','cafe','retail'])], // 👈 new
            'from_location_id' => ['required','integer','exists:locations,id'],
            'to_location_id'   => ['required','integer','exists:locations,id','different:from_location_id'],
            'quantity'         => ['required','integer','min:1'],
            'meta'             => ['nullable','array'],
        ]);

        $industryStrategy = $this->resolver::resolve($validated['industry']); // 👈 resolve by industry
        $movement = $industryStrategy->moveStock(
            $product,
            (int)$validated['from_location_id'],
            (int)$validated['to_location_id'],
            (int)$validated['quantity'],
            $validated['meta'] ?? []
        );

        return response()->json($movement);
    }
}
