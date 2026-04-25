<?php

namespace App\Http\Controllers\Api\Tenant;

use Throwable;
use ZipArchive;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Tenant\Product;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\Storage;
use App\Services\ProductManagement\Strategies\ProductStrategyResolver;

use App\Jobs\ProcessProductImagesZip;

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
            'inventory' => ['nullable', 'array'],
            'track_inventory' => ['nullable', 'boolean']
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
            'location_id'  => ['nullable','int'],
            'type'     => ['nullable', Rule::in(['basic','raw','semi_finished','finished','recipe','other'])],
        ]);
        
        $industryStrategy = $this->resolver::resolve($validated['industry']); // 👈 resolve by industry
        $items = $industryStrategy->search(
            $validated['keyword'] ?? null, 
            $validated['type'] ?? null, 
            $validated['location_id'] ?? null
        );

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

    public function bulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $created = 0;
        $failed = [];
        $now = now();

        DB::beginTransaction();

        try {

            $file = $request->file('file');
            
            LazyCollection::make(function () use ($file) {
                $handle = fopen($file->getRealPath(), 'r');

                $header = fgetcsv($handle);
                if (!$header) {
                    fclose($handle);
                    return;
                }

                $headerCount = count($header);

                while (($row = fgetcsv($handle)) !== false) {

                    // ✅ Skip empty rows
                    if (count(array_filter($row)) === 0) {
                        continue;
                    }

                    // // ✅ Fix column mismatch
                    // if (count($row) !== $headerCount) {
                    //     // Option 1: skip bad row
                    //     continue;

                    //     // Option 2 (alternative): pad row
                    //     // $row = array_pad($row, $headerCount, null);
                    // }

                    // Allow missing optional columns
                    if (count($row) < $headerCount) {
                        $row = array_pad($row, $headerCount, null);
                    }


                    yield array_combine($header, $row);
                }

                fclose($handle);
            })
            ->chunk(500) // 🔥 memory + performance sweet spot
            ->each(function ($rows) use (&$created, &$failed, $now, $request) {

                $products = [];
                $inventories = [];

                foreach ($rows as $index => $row) {
                    try {
                        // Basic validation
                        if (empty($row['name']) || empty($row['sku'])) {
                            throw new \Exception('Name or SKU missing');
                        }

                        $created++;
                        $industryStrategy = $this->resolver::resolve($request['industry']); // 👈 resolve by industry
                        $productPayload = $industryStrategy->getProductPayload($row);
                        $updated = $industryStrategy->create($productPayload);
                    } catch (\Throwable $e) {
                        $failed[] = [
                            'row' => $row,
                            'error' => $e->getMessage(),
                        ];
                    }
                }

            });

            DB::commit();

            return [
                'status' => 'success',
                'created' => $created,
                'failed' => count($failed),
                'errors' => $failed
            ];

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkImageUpload(Request $request)
    {
        $request->validate([
            'zip' => 'required|file|mimes:zip',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $zipFile = $request->file('zip');
        $zipFileName = $zipFile->getClientOriginalName();

        $tempFolder = 'tenants/' . $tenantId . '/products/temp';
        $tempPath = $tempFolder . '/' . $zipFileName;

        Storage::disk('public')->putFileAs($tempFolder, $zipFile, $zipFileName);

        // 🚀 Dispatch job
        ProcessProductImagesZip::dispatch($tempPath, $tenantId);

        return response()->json([
            'message' => 'Upload started. Images are being processed in background.'
        ]);
    }
}
