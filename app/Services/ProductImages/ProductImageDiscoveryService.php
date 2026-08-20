<?php

namespace App\Services\ProductImages;

use App\Models\Tenant;
use App\Models\Tenant\ExternalProductImageSuggestion;
use App\Models\Tenant\Product;
use App\Models\Tenant\ProductImage;
use App\Services\ProductImages\Contracts\ProductImageProviderInterface;
use App\Support\Observability;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductImageDiscoveryService
{
    private const CACHE_TTL_HOURS = 24;
    private const TENANT_HOUR_LIMIT = 30;
    private const TENANT_DAY_LIMIT = 200;
    private const USER_MINUTE_LIMIT = 10;

    public function __construct(
        private ProductImageProviderInterface $provider,
        private ProductImageQueryBuilder $queries,
    ) {
    }

    public function suggest(Product $product, Tenant $tenant, array $options = []): array
    {
        $refresh = (bool) ($options['refresh'] ?? false);
        $force = (bool) ($options['force'] ?? false);
        $provider = $this->provider->name();
        $query = $this->queries->build($product, $tenant);

        if (! $force && $this->hasMerchantImage($product)) {
            return [
                'success' => true,
                'has_image' => true,
                'cached' => false,
                'provider' => $provider,
                'query' => $query,
                'suggestions' => [],
                'fallback' => 'Product already has a merchant image.',
            ];
        }

        $cached = $this->cachedSuggestions($product, $provider, $query);
        if (! $refresh && $cached->isNotEmpty()) {
            return [
                'success' => true,
                'has_image' => false,
                'cached' => true,
                'provider' => $provider,
                'query' => $query,
                'suggestions' => $cached->values(),
                'fallback' => null,
            ];
        }

        $quota = $this->checkQuota($tenant, $provider);
        if (! $quota['allowed']) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'code' => 'PRODUCT_IMAGE_QUOTA_EXCEEDED',
                'message' => 'Image suggestion quota reached. Try again later.',
                'provider' => $provider,
                'query' => $query,
                'suggestions' => $cached->values(),
            ], 429));
        }

        $result = $this->provider->search($query, 5);

        if (! ($result['success'] ?? false)) {
            ExternalProductImageSuggestion::create([
                'product_id' => $product->id,
                'provider' => $provider,
                'query' => $query,
                'status' => 'failed',
                'error_message' => $result['message'] ?? 'Image search failed.',
                'searched_at' => now(),
                'meta' => ['code' => $result['code'] ?? 'provider_unavailable'],
            ]);

            return [
                'success' => false,
                'code' => $result['code'] ?? 'provider_unavailable',
                'message' => $result['message'] ?? 'Image search failed.',
                'has_image' => false,
                'cached' => false,
                'provider' => $provider,
                'query' => $query,
                'suggestions' => $cached->values(),
                'fallback' => 'Use the product/category placeholder or upload an image.',
            ];
        }

        $suggestions = collect($result['results'] ?? [])
            ->map(fn (array $item) => $this->persistSuggestion($product, $query, $item))
            ->values();

        return [
            'success' => true,
            'has_image' => false,
            'cached' => false,
            'provider' => $provider,
            'query' => $query,
            'suggestions' => $suggestions,
            'fallback' => $suggestions->isEmpty() ? 'No suitable image found.' : null,
        ];
    }

    public function accept(Product $product, ExternalProductImageSuggestion $suggestion, Tenant $tenant): Product
    {
        if ((int) $suggestion->product_id !== (int) $product->id) {
            throw ValidationException::withMessages([
                'suggestion' => 'Image suggestion does not belong to this product.',
            ]);
        }

        if (! $suggestion->full_url && ! $suggestion->preview_url) {
            throw ValidationException::withMessages([
                'suggestion' => 'Image suggestion does not have a downloadable URL.',
            ]);
        }

        $path = $this->storeExternalImage($product, $suggestion, $tenant);
        $payload = [
            'image_path' => $path,
            'source' => 'external_approved',
            'provider' => $suggestion->provider,
            'provider_image_id' => $suggestion->provider_image_id,
            'provider_url' => $suggestion->full_url ?: $suggestion->preview_url,
            'author_name' => $suggestion->photographer_name,
            'author_url' => $suggestion->photographer_url,
            'license' => $suggestion->license,
            'meta' => ['suggestion_id' => $suggestion->id],
            'is_primary' => false,
        ];

        if (! Schema::hasColumn('product_images', 'source')) {
            $payload = ['image_path' => $path];
        }

        $identity = ['product_id' => $product->id, 'image_path' => $path];
        if (Schema::hasColumn('product_images', 'provider') && Schema::hasColumn('product_images', 'provider_image_id')) {
            $identity = [
                'product_id' => $product->id,
                'provider' => $suggestion->provider,
                'provider_image_id' => $suggestion->provider_image_id,
            ];
        }

        ProductImage::updateOrCreate($identity, array_merge(['product_id' => $product->id], $payload));

        $suggestion->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return $product->fresh(['images', 'categories:id,name,description', 'inventories', 'recipe.items']);
    }

    public function reject(Product $product, ExternalProductImageSuggestion $suggestion): ExternalProductImageSuggestion
    {
        if ((int) $suggestion->product_id !== (int) $product->id) {
            throw ValidationException::withMessages([
                'suggestion' => 'Image suggestion does not belong to this product.',
            ]);
        }

        $suggestion->update(['status' => 'rejected']);

        return $suggestion->fresh();
    }

    private function persistSuggestion(Product $product, string $query, array $item): array
    {
        $suggestion = ExternalProductImageSuggestion::updateOrCreate(
            [
                'product_id' => $product->id,
                'provider' => $item['provider'],
                'provider_image_id' => $item['provider_image_id'],
            ],
            [
                'query' => $query,
                'preview_url' => $item['preview_url'] ?? null,
                'full_url' => $item['full_url'] ?? null,
                'photographer_name' => $item['photographer_name'] ?? null,
                'photographer_url' => $item['photographer_url'] ?? null,
                'license' => $item['license'] ?? null,
                'status' => 'suggested',
                'error_message' => null,
                'searched_at' => now(),
                'meta' => $item['meta'] ?? null,
            ]
        );

        return $this->suggestionPayload($suggestion);
    }

    private function cachedSuggestions(Product $product, string $provider, string $query)
    {
        return ExternalProductImageSuggestion::query()
            ->where('product_id', $product->id)
            ->where('provider', $provider)
            ->where('query', $query)
            ->where('status', 'suggested')
            ->where('searched_at', '>=', now()->subHours(self::CACHE_TTL_HOURS))
            ->latest('searched_at')
            ->get()
            ->map(fn (ExternalProductImageSuggestion $suggestion) => $this->suggestionPayload($suggestion));
    }

    private function suggestionPayload(ExternalProductImageSuggestion $suggestion): array
    {
        return [
            'id' => $suggestion->id,
            'provider' => $suggestion->provider,
            'query' => $suggestion->query,
            'provider_image_id' => $suggestion->provider_image_id,
            'preview_url' => $suggestion->preview_url,
            'full_url' => $suggestion->full_url,
            'photographer_name' => $suggestion->photographer_name,
            'photographer_url' => $suggestion->photographer_url,
            'license' => $suggestion->license,
            'status' => $suggestion->status,
            'searched_at' => optional($suggestion->searched_at)->toISOString(),
        ];
    }

    private function hasMerchantImage(Product $product): bool
    {
        $product->loadMissing('images');

        return $product->images->contains(function (ProductImage $image) {
            $source = $image->source ?: 'merchant_upload';

            return in_array($source, ['merchant_upload', 'bulk_upload', 'imported_path'], true);
        });
    }

    private function checkQuota(Tenant $tenant, string $provider): array
    {
        $userId = auth()->id() ?: 'guest';
        $keys = [
            ['key' => "product-image:{$tenant->id}:{$provider}:hour:".now()->format('YmdH'), 'limit' => self::TENANT_HOUR_LIMIT, 'ttl' => now()->addHour()],
            ['key' => "product-image:{$tenant->id}:{$provider}:day:".now()->format('Ymd'), 'limit' => self::TENANT_DAY_LIMIT, 'ttl' => now()->addDay()],
            ['key' => "product-image:{$tenant->id}:{$provider}:user:{$userId}:minute:".now()->format('YmdHi'), 'limit' => self::USER_MINUTE_LIMIT, 'ttl' => now()->addMinute()],
        ];

        foreach ($keys as $entry) {
            $count = (int) Cache::get($entry['key'], 0);
            if ($count >= $entry['limit']) {
                return ['allowed' => false, 'key' => $entry['key']];
            }
        }

        foreach ($keys as $entry) {
            if (! Cache::has($entry['key'])) {
                Cache::put($entry['key'], 1, $entry['ttl']);
            } else {
                Cache::increment($entry['key']);
            }
        }

        return ['allowed' => true];
    }

    private function storeExternalImage(Product $product, ExternalProductImageSuggestion $suggestion, Tenant $tenant): string
    {
        $url = $suggestion->full_url ?: $suggestion->preview_url;
        $extension = pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: 'jpg';
        $extension = strtolower(preg_replace('/[^a-z0-9]/i', '', $extension)) ?: 'jpg';
        $filename = Str::slug($suggestion->provider.'-'.$suggestion->provider_image_id) ?: 'external-image';
        $path = "tenants/{$tenant->id}/products/external/{$product->id}/{$filename}.{$extension}";

        if (Storage::disk('public')->exists($path)) {
            return $path;
        }

        try {
            $response = Http::timeout(8)->get($url);
            if (! $response->successful() || $response->body() === '') {
                throw ValidationException::withMessages([
                    'suggestion' => 'Unable to download the selected image.',
                ]);
            }

            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Observability::logWarning('product_image_provider.download_exception', $e, [
                'provider' => $suggestion->provider,
                'suggestion_id' => $suggestion->id,
            ]);

            throw ValidationException::withMessages([
                'suggestion' => 'Unable to download the selected image.',
            ]);
        }
    }
}
