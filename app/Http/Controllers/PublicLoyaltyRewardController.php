<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Generator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicLoyaltyRewardController extends Controller
{
    public function show(Request $request, string $token, LoyaltyService $loyalty)
    {
        $tenant = $this->tenantFromToken($token);
        $tenant->loadMissing('branding');
        $brandName = $tenant->branding?->company_name ?: $tenant->name ?: str($tenant->slug)->headline()->toString();

        $this->configureTenant($tenant);
        app()->instance('currentTenant', $tenant);

        $payload = $loyalty->rewardPayloadForToken($token);

        if (! $payload) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Reward link is invalid or expired.'], 404);
            }

            throw new NotFoundHttpException('Reward link is invalid or expired.');
        }

        $payload['tenant'] = [
            'id' => $tenant->id,
            'slug' => $tenant->slug,
            'name' => $brandName,
        ];

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        $qrSvg = $this->qrSvg((string) $payload['qr_payload']);

        return response()
            ->view('loyalty.reward', [
                'reward' => $payload,
                'qrSvg' => $qrSvg,
                'brandName' => $brandName,
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function tenantFromToken(string $token): Tenant
    {
        [$slug] = array_pad(explode('.', $token, 2), 2, null);

        if (! is_string($slug) || ! preg_match('/^[A-Za-z0-9_-]+$/', $slug)) {
            throw new NotFoundHttpException('Reward link is invalid or expired.');
        }

        $tenant = Tenant::query()->where('slug', str_replace('_', '-', $slug))->first()
            ?: Tenant::query()->where('slug', $slug)->first();

        if (! $tenant) {
            throw new NotFoundHttpException('Reward link is invalid or expired.');
        }

        return $tenant;
    }

    private function configureTenant(Tenant $tenant): void
    {
        $base = config('database.connections.mysql');

        Config::set('database.connections.tenant', array_merge($base, [
            'database' => $tenant->database,
        ]));

        DB::purge('tenant');
        DB::setDefaultConnection('tenant');
        DB::reconnect('tenant');
    }

    private function qrSvg(string $payload): ?string
    {
        try {
            return (new Generator())->format('svg')->size(280)->margin(1)->generate($payload);
        } catch (\Throwable) {
            return null;
        }
    }
}
