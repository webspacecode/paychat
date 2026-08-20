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
        $disk = Storage::disk('public');
        $exists = $disk->exists($path);
        $generated = false;

        if ($refresh || ! $exists) {
            $disk->put($path, $this->qrSvg($targetUrl));
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
        $configured = config('services.frontend_url')
            ?: config('app.frontend_url')
            ?: env('FRONTEND_URL')
            ?: env('POS_FRONTEND_URL');

        if ($configured) {
            return rtrim((string) $configured, '/');
        }

        return rtrim($request->getSchemeAndHttpHost() ?: config('app.url'), '/');
    }

    private function tenantQrPath(Tenant $tenant): string
    {
        return "tenants/{$tenant->id}/self-pos/self-pos.svg";
    }

    private function qrSvg(string $url): string
    {
        return (new Generator())->format('svg')->size(360)->margin(1)->generate($url);
    }
}
