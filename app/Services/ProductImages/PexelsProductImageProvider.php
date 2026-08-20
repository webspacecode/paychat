<?php

namespace App\Services\ProductImages;

use App\Services\ProductImages\Contracts\ProductImageProviderInterface;
use App\Support\Observability;
use Illuminate\Support\Facades\Http;
use Throwable;

class PexelsProductImageProvider implements ProductImageProviderInterface
{
    public function name(): string
    {
        return 'pexels';
    }

    public function search(string $query, int $limit = 5): array
    {
        if (! config('services.pexels.enabled', true) || ! config('services.pexels.key')) {
            return [
                'success' => false,
                'code' => 'provider_unavailable',
                'message' => 'Pexels image search is not configured.',
                'results' => [],
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => config('services.pexels.key'),
            ])
                ->timeout(5)
                ->get(rtrim((string) config('services.pexels.base_url', 'https://api.pexels.com/v1'), '/').'/search', [
                    'query' => $query,
                    'per_page' => max(1, min($limit, 10)),
                    'orientation' => 'square',
                ]);

            if ($response->status() === 429) {
                return [
                    'success' => false,
                    'code' => 'provider_rate_limited',
                    'message' => 'Image provider rate limit reached.',
                    'results' => [],
                ];
            }

            if (! $response->successful()) {
                Observability::logWarningMessage('product_image_provider.search_failed', [
                    'provider' => $this->name(),
                    'status' => $response->status(),
                ]);

                return [
                    'success' => false,
                    'code' => 'provider_unavailable',
                    'message' => 'Image provider is unavailable.',
                    'results' => [],
                ];
            }

            $photos = $response->json('photos') ?: [];

            return [
                'success' => true,
                'code' => null,
                'message' => null,
                'results' => collect($photos)->map(fn (array $photo) => [
                    'provider' => $this->name(),
                    'provider_image_id' => (string) ($photo['id'] ?? ''),
                    'preview_url' => $photo['src']['medium'] ?? $photo['src']['small'] ?? null,
                    'full_url' => $photo['src']['large2x'] ?? $photo['src']['large'] ?? $photo['src']['original'] ?? null,
                    'photographer_name' => $photo['photographer'] ?? null,
                    'photographer_url' => $photo['photographer_url'] ?? null,
                    'license' => 'Pexels',
                    'meta' => [
                        'url' => $photo['url'] ?? null,
                        'avg_color' => $photo['avg_color'] ?? null,
                        'width' => $photo['width'] ?? null,
                        'height' => $photo['height'] ?? null,
                    ],
                ])->filter(fn (array $item) => $item['provider_image_id'] && ($item['preview_url'] || $item['full_url']))->values()->all(),
            ];
        } catch (Throwable $e) {
            Observability::logWarning('product_image_provider.search_exception', $e, [
                'provider' => $this->name(),
            ]);

            return [
                'success' => false,
                'code' => 'provider_unavailable',
                'message' => 'Image provider is unavailable.',
                'results' => [],
            ];
        }
    }
}
