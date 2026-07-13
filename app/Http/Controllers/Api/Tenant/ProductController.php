<?php

namespace App\Http\Controllers\Api\Tenant;

use Throwable;
use ZipArchive;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Tenant\Product;
use App\Models\Tenant\Location;
use App\Models\Tenant\StockMovement;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\Storage;
use App\Support\IndustryNormalizer;
use App\Services\ProductManagement\Strategies\ProductStrategyResolver;
use App\Services\ProductManagement\ProductApplicationService;

use App\Jobs\ProcessProductImagesZip;

class ProductController extends Controller
{
    public function __construct(private ProductStrategyResolver $resolver, private ProductApplicationService $products) {
        
    }

    // CREATE
    public function store(Request $request)
    {
        $this->normalizeIndustryInput($request);

        $validated = $request->validate([
            'industry' => ['required', Rule::in(IndustryNormalizer::productIndustries())],
            'name'  => ['required','string','max:255'],
            'sku'   => ['nullable','string','max:255','unique:products,sku'],
            'barcode' => ['nullable','string','max:255','unique:products,barcode'],
            'type'  => ['nullable', Rule::in(['basic','raw','semi_finished','finished','recipe','other'])],
            'price' => ['nullable','numeric','min:0'],
            'unit'  => ['nullable','string','max:50'],
            'images'=> ['sometimes','array'],
            'images.*' => ['nullable'],
            'image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            // recipe fields:
            'location_id' => ['nullable','integer','exists:locations,id'],
            'description' => ['nullable','string'],
            'items'       => ['sometimes','array'],
            'items.*.raw_product_id' => ['required_with:items','integer','exists:products,id'],
            'items.*.quantity'       => ['required_with:items','integer','min:1'],
            'items.*.unit'           => ['nullable','string','max:50'],
            'inventory' => ['nullable', 'array'],
            'track_inventory' => ['nullable', 'boolean'],
            'low_stock_threshold' => ['nullable','integer','min:0'],
            'is_active' => ['nullable','boolean'],
            'categories' => ['nullable'],
        ]);

        $validated = $this->prepareProductPayload($request, $validated, true);
        $validated = $this->applySimpleBillingProductDefaults($validated);
        $product = $this->products->create($validated);

        return response()->json($product, 201);
    }

    // READ (list/search with filters)
    public function index(Request $request)
    {
        $this->normalizeIndustryInput($request);

        $validated = $request->validate([
            'industry' => ['required', Rule::in(IndustryNormalizer::productIndustries())], // 👈 new
            'keyword'  => ['nullable','string'],
            'location_id'  => ['nullable','int'],
            'type'     => ['nullable', Rule::in(['basic','raw','semi_finished','finished','recipe','other'])],
            'include_inactive' => ['nullable','boolean'],
        ]);
        
        $industryStrategy = $this->resolver::resolve($validated['industry']); // 👈 resolve by industry
        $items = $industryStrategy->search(
            $validated['keyword'] ?? null, 
            $validated['type'] ?? null, 
            $validated['location_id'] ?? null,
            (bool) ($validated['include_inactive'] ?? false)
        );

        return response()->json($items);
    }

    // SHOW
    public function show(Request $request, $tenantSlug, int $id)
    {
        $this->normalizeIndustryInput($request);

        $validated = $request->validate([
            'industry' => ['required', Rule::in(IndustryNormalizer::productIndustries())], // 👈 new
        ]);

        $industryStrategy = $this->resolver::resolve($validated['industry']); // 👈 resolve by industry
        $product  = $industryStrategy->getById($id);

        return $product
            ? response()->json($product)
            : response()->json(['message' => 'Product not found'], 404);
    }

    // UPDATE
    public function update(Request $request, $tenantSlug, $product)
    {
        $this->normalizeIndustryInput($request);

        $productId = $this->routeProductId($product);

        $validated = $request->validate([
            'industry' => ['required', Rule::in(IndustryNormalizer::productIndustries())], // 👈 new
            'name'  => ['sometimes','string','max:255'],
            'sku'   => ['nullable','string','max:255', Rule::unique('products', 'sku')->ignore($productId)],
            'barcode' => ['nullable','string','max:255', Rule::unique('products', 'barcode')->ignore($productId)],
            'type'  => ['sometimes', Rule::in(['basic','raw','semi_finished','finished','recipe','other'])],
            'price' => ['nullable','numeric','min:0'],
            'unit'  => ['nullable','string','max:50'],
            'images'=> ['sometimes','array'],
            'images.*' => ['nullable'],
            'image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],

            // recipe:
            'location_id' => ['nullable','integer','exists:locations,id'],
            'description' => ['nullable','string'],
            'items'       => ['sometimes','array'],
            'items.*.raw_product_id' => ['required_with:items','integer','exists:products,id'],
            'items.*.quantity'       => ['required_with:items','integer','min:1'],
            'items.*.unit'           => ['nullable','string','max:50'],
            'track_inventory' => ['nullable', 'boolean'],
            'low_stock_threshold' => ['nullable','integer','min:0'],
            'is_active' => ['nullable','boolean'],
            'categories' => ['nullable'],
        ]);

        $validated = $this->prepareProductPayload($request, $validated, false);
        $validated = $this->applySimpleBillingProductDefaults($validated);
        $product = $this->resolveRouteProduct($productId);
        $updated = $this->products->update($product, $validated);

        return response()->json($updated);
    }

    // DELETE
    public function destroy(Request $request, $tenantSlug, $product)
    {
        $this->normalizeIndustryInput($request);

        $validated = $request->validate([
            'industry' => ['required', Rule::in(IndustryNormalizer::productIndustries())], // 👈 new
        ]);

        $industryStrategy = $this->resolver::resolve($validated['industry']); // 👈 resolve by industry
        $product = $this->resolveRouteProduct($product);
        $industryStrategy->delete($product);

        return response()->json(['message' => 'Product disabled successfully']);
    }

    // INVENTORY: adjust (+/-)
    public function adjustInventory(Request $request, $tenantSlug, $product)
    {
        $this->normalizeIndustryInput($request);

        $validated = $request->validate([
            'industry'    => ['required', Rule::in(IndustryNormalizer::productIndustries())], // 👈 new
            'location_id' => ['required','integer','exists:locations,id'],
            'delta_qty'   => ['required','integer','not_in:0'],
            'meta'        => ['nullable','array'],
        ]);

        $industryStrategy = $this->resolver::resolve($validated['industry']); // 👈 resolve by industry
        $product = $this->resolveRouteProduct($product);
        $inventory = $industryStrategy->adjustInventory($product, (int)$validated['location_id'], (int)$validated['delta_qty'], $validated['meta'] ?? []);

        return response()->json($inventory);
    }

    public function stockMovements(Request $request, $tenantSlug, $product)
    {
        $validated = $request->validate([
            'location_id' => ['nullable','integer','exists:locations,id'],
            'limit' => ['nullable','integer','min:1','max:50'],
        ]);
        $product = $this->resolveRouteProduct($product);

        $limit = (int) ($validated['limit'] ?? 20);
        $locationId = $validated['location_id'] ?? null;

        $movements = StockMovement::query()
            ->where('product_id', $product->id)
            ->when($locationId, function ($query) use ($locationId) {
                $query->where(function ($locations) use ($locationId) {
                    $locations->where('from_location_id', $locationId)
                        ->orWhere('to_location_id', $locationId);
                });
            })
            ->latest()
            ->limit($limit)
            ->get();

        $locationIds = $movements
            ->flatMap(fn ($movement) => [$movement->from_location_id, $movement->to_location_id])
            ->filter()
            ->unique()
            ->values();

        $locations = Location::whereIn('id', $locationIds)->pluck('name', 'id');

        return response()->json($movements->map(function (StockMovement $movement) use ($locations) {
            return [
                'id' => $movement->id,
                'type' => $movement->type,
                'quantity' => $movement->quantity,
                'from_location_id' => $movement->from_location_id,
                'from_location_name' => $movement->from_location_id ? ($locations[$movement->from_location_id] ?? null) : null,
                'to_location_id' => $movement->to_location_id,
                'to_location_name' => $movement->to_location_id ? ($locations[$movement->to_location_id] ?? null) : null,
                'order_id' => $movement->order_id,
                'meta' => $movement->meta,
                'created_at' => $movement->created_at,
            ];
        }));
    }

    // INVENTORY: transfer
    public function moveStock(Request $request, $tenantSlug, $product)
    {
        $this->normalizeIndustryInput($request);

        $validated = $request->validate([
            'industry'        => ['required', Rule::in(IndustryNormalizer::productIndustries())], // 👈 new
            'from_location_id' => ['required','integer','exists:locations,id'],
            'to_location_id'   => ['required','integer','exists:locations,id','different:from_location_id'],
            'quantity'         => ['required','integer','min:1'],
            'meta'             => ['nullable','array'],
        ]);

        $industryStrategy = $this->resolver::resolve($validated['industry']); // 👈 resolve by industry
        $product = $this->resolveRouteProduct($product);
        $movement = $industryStrategy->moveStock(
            $product,
            (int)$validated['from_location_id'],
            (int)$validated['to_location_id'],
            (int)$validated['quantity'],
            $validated['meta'] ?? []
        );

        return response()->json($movement);
    }

    private function routeProductId($product): int
    {
        if ($product instanceof Product) {
            return (int) $product->id;
        }

        abort_unless(is_numeric($product), 404, 'Product not found');

        return (int) $product;
    }

    private function resolveRouteProduct($product): Product
    {
        if ($product instanceof Product) {
            return $product;
        }

        $product = Product::query()->whereKey($this->routeProductId($product))->first();
        abort_unless($product, 404, 'Product not found');

        return $product;
    }

    public function bulkUpload(Request $request)
    {
        $this->normalizeIndustryInput($request);

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

                $header = array_map(function ($column) {
                    return trim((string) $column, " \t\n\r\0\x0B\xEF\xBB\xBF");
                }, $header);

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

                    $row = array_slice($row, 0, $headerCount);

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

                        $industryStrategy = $this->resolver::resolve($request['industry']); // 👈 resolve by industry
                        $productPayload = $industryStrategy->getProductPayload($row);
                        $productPayload = $this->applySimpleBillingProductDefaults(
                            array_merge($productPayload, ['industry' => $request['industry']])
                        );
                        $updated = $industryStrategy->create($productPayload);
                        $created++;
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

    private function prepareProductPayload(Request $request, array $data, bool $generateSku): array
    {
        $data['type'] = $data['type'] ?? 'basic';

        if ($generateSku && empty($data['sku'])) {
            $base = Str::upper(Str::slug($data['name'] ?? 'ITEM', ''));
            $data['sku'] = substr($base ?: 'ITEM', 0, 18) . '-' . Str::upper(Str::random(5));
        } elseif (array_key_exists('sku', $data) && $data['sku'] === '') {
            $data['sku'] = null;
        }

        foreach (['barcode', 'unit'] as $nullableTextField) {
            if (array_key_exists($nullableTextField, $data) && $data[$nullableTextField] === '') {
                $data[$nullableTextField] = null;
            }
        }

        if ($request->hasFile('image')) {
            $data['images'] = $data['images'] ?? [];
            $data['images'][] = $request->file('image');
        }

        if (array_key_exists('low_stock_threshold', $data) && $data['low_stock_threshold'] === '') {
            $data['low_stock_threshold'] = null;
        }

        return $data;
    }

    private function applySimpleBillingProductDefaults(array $data): array
    {
        if (! IndustryNormalizer::isSimpleBilling($data['industry'] ?? null)) {
            return $data;
        }

        $data['type'] = $data['type'] ?? 'basic';

        if (! array_key_exists('track_inventory', $data)) {
            $data['track_inventory'] = false;
        }

        return $data;
    }

    private function normalizeIndustryInput(Request $request): void
    {
        if (! $request->has('industry')) {
            return;
        }

        $request->merge([
            'industry' => IndustryNormalizer::normalize($request->input('industry')),
        ]);
    }
}
