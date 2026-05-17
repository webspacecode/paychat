<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Category;
use App\Services\ProductManagement\Strategies\CategoryStrategyResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

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

    public function bulkUpload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'industry' => 'nullable|string',
        ]);

        $industry = $validated['industry'] ?? 'default';
        $strategy = $this->resolver->resolve($industry);
        $created = 0;
        $updated = 0;
        $failed = [];

        DB::beginTransaction();

        try {
            LazyCollection::make(function () use ($request) {
                $handle = fopen($request->file('file')->getRealPath(), 'r');
                $header = fgetcsv($handle);

                if (!$header) {
                    fclose($handle);
                    return;
                }

                $header = array_map(function ($column) {
                    return strtolower(trim($column));
                }, $header);

                $headerCount = count($header);
                $rowNumber = 1;

                while (($row = fgetcsv($handle)) !== false) {
                    $rowNumber++;

                    if (count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
                        continue;
                    }

                    if (count($row) < $headerCount) {
                        $row = array_pad($row, $headerCount, null);
                    }

                    if (count($row) > $headerCount) {
                        $row = array_slice($row, 0, $headerCount);
                    }

                    yield [
                        'row_number' => $rowNumber,
                        'data' => array_combine($header, $row),
                    ];
                }

                fclose($handle);
            })
                ->chunk(500)
                ->each(function ($rows) use (&$created, &$updated, &$failed, $strategy) {
                    foreach ($rows as $row) {
                        try {
                            $data = $row['data'];
                            $name = trim((string) ($data['name'] ?? ''));

                            if ($name === '') {
                                throw new \Exception('Name is required');
                            }

                            $payload = [
                                'name' => $name,
                                'description' => $this->nullableCsvValue($data['description'] ?? null),
                            ];

                            $category = Category::where('name', $name)->first();

                            if ($category) {
                                $strategy->update($category, $payload);
                                $updated++;
                            } else {
                                $strategy->create($payload);
                                $created++;
                            }
                        } catch (\Throwable $e) {
                            $failed[] = [
                                'row' => $row['row_number'],
                                'data' => $row['data'],
                                'error' => $e->getMessage(),
                            ];
                        }
                    }
                });

            DB::commit();

            return response()->json([
                'message' => 'Category CSV processed',
                'created' => $created,
                'updated' => $updated,
                'failed_count' => count($failed),
                'failed' => $failed,
            ], count($failed) ? 207 : 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Category CSV upload failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function bulkTemplate()
    {
        $csv = "name,description\nBeverages,Hot and cold drinks\nSnacks,Quick bites and sides\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=\"category_bulk_template.csv\"',
        ]);
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

    private function nullableCsvValue(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
