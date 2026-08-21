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
        $printableExists = $disk->exists($this->tenantPrintableQrPath($tenant));
        $storedTargetUrl = $this->storedTenantQrTargetUrl($disk, $metaPath);
        $targetUnknown = $exists && $storedTargetUrl === null;
        $targetChanged = $exists && $storedTargetUrl !== null && $storedTargetUrl !== $targetUrl;
        $generated = false;

        if ($refresh || ! $exists || ! $printableExists || $targetUnknown || $targetChanged) {
            $disk->put($path, $this->qrSvg($targetUrl));
            $disk->put($this->tenantPrintableQrPath($tenant), $this->printableQrSvg(
                $this->qrSvg($targetUrl),
                $tenant->name ?: 'PayChat',
                'Scan to order',
                'Scan to order quickly — no need to wait at the counter'
            ));
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
            'printable_qr_svg' => $disk->exists($this->tenantPrintableQrPath($tenant))
                ? $disk->get($this->tenantPrintableQrPath($tenant))
                : null,
            'download_url' => $disk->url($this->tenantPrintableQrPath($tenant)),
            'raw_download_url' => $disk->url($path),
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
            'qr_svg' => $qrSvg = $this->qrSvg($targetUrl),
            'printable_qr_svg' => $this->printableQrSvg(
                $qrSvg,
                $tenant->name ?: 'PayChat',
                'Scan to order from this table',
                'Table '.$this->safeLabel($table->name ?: $table->code ?: $table->id)
            ),
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
        $requestOrigins = array_values(array_filter([
            $this->forwardedOrigin($request),
            $this->originFromHeader($request->getSchemeAndHttpHost()),
        ]));

        $browserOrigins = array_values(array_filter([
            $this->originFromHeader($request->headers->get('origin')),
            $this->originFromHeader($request->headers->get('referer')),
        ]));

        $configuredOrigins = array_values(array_filter([
            $this->originFromHeader(config('services.frontend_url')),
            $this->originFromHeader(config('app.frontend_url')),
            $this->originFromHeader(env('FRONTEND_URL')),
            $this->originFromHeader(env('POS_FRONTEND_URL')),
            $this->originFromHeader(config('app.url')),
        ]));

        foreach ([$requestOrigins, $browserOrigins, $configuredOrigins] as $origins) {
            foreach ($origins as $origin) {
                if (! $this->isLocalOrigin($origin)) {
                    return $origin;
                }
            }
        }

        return $browserOrigins[0]
            ?? $configuredOrigins[0]
            ?? $requestOrigins[0]
            ?? 'http://localhost';
    }

    private function forwardedOrigin(Request $request): ?string
    {
        $host = $this->firstForwardedValue($request->headers->get('x-forwarded-host'));

        if (! $host) {
            return null;
        }

        $scheme = $this->firstForwardedValue($request->headers->get('x-forwarded-proto'))
            ?: ($request->headers->get('x-forwarded-ssl') === 'on' ? 'https' : $request->getScheme());

        return $this->originFromHeader("{$scheme}://{$host}");
    }

    private function firstForwardedValue(mixed $value): ?string
    {
        $first = trim(explode(',', (string) $value)[0] ?? '');

        return $first !== '' ? $first : null;
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

    private function isLocalOrigin(string $origin): bool
    {
        $host = strtolower((string) (parse_url($origin, PHP_URL_HOST) ?: ''));

        return $host === 'localhost'
            || $host === '::1'
            || str_ends_with($host, '.localhost')
            || str_starts_with($host, '127.');
    }

    private function tenantQrPath(Tenant $tenant): string
    {
        return "tenants/{$tenant->id}/self-pos/self-pos.svg";
    }

    private function tenantPrintableQrPath(Tenant $tenant): string
    {
        return "tenants/{$tenant->id}/self-pos/self-pos-printable.svg";
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

    private function printableQrSvg(string $qrSvg, string $brand, string $heading, string $instruction): string
    {
        $qr = $this->stripXmlDeclaration($qrSvg);
        $brand = $this->safeLabel($brand);
        $heading = $this->safeLabel($heading);
        $instruction = $this->safeLabel($instruction);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1240" height="1748" viewBox="0 0 1240 1748" role="img" aria-label="{$heading}">
  <rect width="1240" height="1748" fill="#ffffff"/>
  <rect x="96" y="96" width="1048" height="1556" rx="36" fill="#ffffff" stroke="#111827" stroke-width="6"/>
  <text x="620" y="210" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="56" font-weight="800" fill="#111827">{$brand}</text>
  <text x="620" y="315" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="48" font-weight="800" fill="#111827">{$heading}</text>
  <g transform="translate(370 450)">
    <rect x="-36" y="-36" width="572" height="572" rx="18" fill="#ffffff"/>
    <g transform="scale(1.3889)">{$qr}</g>
  </g>
  <text x="620" y="1120" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="40" font-weight="700" fill="#111827">{$instruction}</text>
  <text x="620" y="1210" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="28" font-weight="700" fill="#4b5563">Open camera, scan, and place your order</text>
  <rect x="336" y="1320" width="568" height="120" rx="60" fill="#C9F73B"/>
  <text x="620" y="1394" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="34" font-weight="800" fill="#111827">Powered by PayChat</text>
</svg>
SVG;
    }

    private function stripXmlDeclaration(string $svg): string
    {
        return preg_replace('/<\?xml[^>]*\?>\s*/i', '', $svg) ?: $svg;
    }

    private function safeLabel(mixed $value): string
    {
        return e(preg_replace('/\s+/', ' ', trim((string) $value)) ?: 'PayChat');
    }
}
