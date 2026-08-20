<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Tenant\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Generator;

class SelfPosQrService
{
    public function tenantQr(Tenant $tenant, Request $request, bool $refresh = false): array
    {
        $targetUrl = $this->selfPosUrl($tenant, $request);
        $path = $this->tenantQrPath($tenant);
        $metaPath = $this->tenantQrMetaPath($tenant);
        $disk = Storage::disk('public');
        $exists = $disk->exists($path);
        $storedTargetUrl = $this->storedTenantQrTargetUrl($disk, $metaPath);
        $targetUnknown = $exists && $storedTargetUrl === null;
        $targetChanged = $exists && $storedTargetUrl !== null && $storedTargetUrl !== $targetUrl;
        $generated = false;

        if ($refresh || ! $exists || $targetUnknown || $targetChanged) {
            $disk->put($path, $this->qrSvg($targetUrl));
            $disk->put($metaPath, json_encode([
                'target_url' => $targetUrl,
                'generated_at' => now()->toISOString(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $generated = true;
        }

        $qrSvg = $disk->get($path);

        return [
            'success' => true,
            'target_url' => $targetUrl,
            'qr_public_url' => $disk->url($path),
            'qr_svg' => $qrSvg,
            'download_url' => $disk->url($path),
            'path' => $path,
            'generated' => $generated,
        ];
    }

    public function tableQr(Tenant $tenant, Resource $table, Request $request): array
    {
        abort_unless($table->type === 'table', 404);

        $tableReference = $this->tableQrReference($table);
        $targetUrl = $this->selfPosUrl($tenant, $request, ['table' => $tableReference]);

        return [
            'success' => true,
            'target_url' => $targetUrl,
            'qr_svg' => $this->qrSvg($targetUrl),
            'table_reference' => $tableReference,
            'table' => [
                'id' => $table->id,
                'name' => $table->name,
                'code' => $table->code,
                'area' => $table->area,
                'floor' => $table->floor,
                'capacity' => $table->capacity,
            ],
        ];
    }

    private function tableQrReference(Resource $table): string
    {
        $code = trim((string) $table->code);

        return $code !== '' ? $code : (string) $table->id;
    }

    private function selfPosUrl(Tenant $tenant, Request $request, array $query = []): string
    {
        $origin = $this->frontendOrigin($request);
        $url = rtrim($origin, '/').'/pos#/self-pos/'.rawurlencode((string) $tenant->api_key);

        if ($query) {
            $url .= '?'.http_build_query($query);
        }

        return $url;
    }

    private function frontendOrigin(Request $request): string
    {
        $requestOrigin = $this->originFromHeader($request->headers->get('origin'))
            ?: $this->originFromHeader($request->headers->get('referer'));

        if ($requestOrigin) {
            return $requestOrigin;
        }

        $configured = $this->originFromHeader(config('services.frontend_url'))
            ?: $this->originFromHeader(config('app.frontend_url'))
            ?: $this->originFromHeader(env('FRONTEND_URL'))
            ?: $this->originFromHeader(env('POS_FRONTEND_URL'));

        if ($configured) {
            return $configured;
        }

        return rtrim($request->getSchemeAndHttpHost() ?: config('app.url'), '/');
    }

    private function originFromHeader(mixed $value): ?string
    {
        $url = trim((string) $value);

        if ($url === '') {
            return null;
        }

        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = $parts['host'] ?? null;

        if (! in_array($scheme, ['http', 'https'], true) || ! $host) {
            return null;
        }

        $origin = "{$scheme}://{$host}";

        if (isset($parts['port'])) {
            $origin .= ":{$parts['port']}";
        }

        return rtrim($origin, '/');
    }

    private function tenantQrPath(Tenant $tenant): string
    {
        return "tenants/{$tenant->id}/self-pos/self-pos.svg";
    }

    private function tenantQrMetaPath(Tenant $tenant): string
    {
        return "tenants/{$tenant->id}/self-pos/self-pos.json";
    }

    private function storedTenantQrTargetUrl($disk, string $path): ?string
    {
        if (! $disk->exists($path)) {
            return null;
        }

        $meta = json_decode((string) $disk->get($path), true);

        return is_array($meta) && isset($meta['target_url'])
            ? (string) $meta['target_url']
            : null;
    }

    private function qrSvg(string $url): string
    {
        return (new Generator())->format('svg')->size(360)->margin(1)->generate($url);
    }
}
