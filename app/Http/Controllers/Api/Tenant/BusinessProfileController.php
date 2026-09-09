<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Branding;
use App\Models\TaxConfig;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class BusinessProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($this->payload($request));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'gst_registered' => ['nullable', 'boolean'],
            'gstin' => ['nullable', 'string', 'max:20'],
            'pan' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $tenant = $this->tenant($request);
        $hasTenantEmail = Schema::connection('mysql')->hasColumn('tenants', 'email');
        $hasTenantPhone = Schema::connection('mysql')->hasColumn('tenants', 'phone');
        $hasTenantAddress = Schema::connection('mysql')->hasColumn('tenants', 'address');
        $hasTenantLogo = Schema::connection('mysql')->hasColumn('tenants', 'logo');
        $hasTenantPan = Schema::connection('mysql')->hasColumn('tenants', 'pan_number');

        DB::connection('mysql')->transaction(function () use ($request, $validated, $tenant, $hasTenantEmail, $hasTenantPhone, $hasTenantAddress, $hasTenantLogo, $hasTenantPan) {
            $logoPath = $request->hasFile('logo')
                ? $this->storeLogo($tenant, $request)
                : null;

            $tenantData = ['name' => $validated['business_name']];
            if ($hasTenantEmail) {
                $tenantData['email'] = $validated['email'] ?? null;
            }
            if ($hasTenantPhone) {
                $tenantData['phone'] = $validated['phone'] ?? null;
            }
            if ($hasTenantAddress) {
                $tenantData['address'] = $validated['address'] ?? null;
            }
            if ($hasTenantLogo && $logoPath) {
                $tenantData['logo'] = $logoPath;
            }
            if ($hasTenantPan) {
                $tenantData['pan_number'] = $validated['pan'] ?? null;
            }
            $tenant->update($tenantData);

            Branding::updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'company_name' => $validated['business_name'],
                    'logo' => $logoPath ?: $tenant->branding?->logo,
                    'primary_color' => $tenant->branding?->primary_color ?: '#4F46E5',
                    'phone' => $validated['phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                ]
            );

            TaxConfig::updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'gst_number' => $validated['gstin'] ?? null,
                    'is_gst_enabled' => (bool) ($validated['gst_registered'] ?? false),
                    'is_inclusive' => $tenant->taxConfig?->is_inclusive ?? false,
                    'cgst_rate' => $tenant->taxConfig?->cgst_rate ?? 0,
                    'sgst_rate' => $tenant->taxConfig?->sgst_rate ?? 0,
                    'igst_rate' => $tenant->taxConfig?->igst_rate ?? 0,
                ]
            );
        });

        return response()->json($this->payload($request));
    }

    private function payload(Request $request): array
    {
        $tenant = $this->tenant($request)->fresh(['branding', 'taxConfig']);
        $hasTenantEmail = Schema::connection('mysql')->hasColumn('tenants', 'email');
        $hasTenantPan = Schema::connection('mysql')->hasColumn('tenants', 'pan_number');

        return [
            'business' => [
                'business_name' => $tenant->branding?->company_name ?: $tenant->name,
                'legal_name' => null,
                'email' => $hasTenantEmail ? $tenant->email : $request->user()?->email,
                'phone' => $tenant->branding?->phone ?? $tenant->phone,
                'logo' => $tenant->branding?->logo ?? $tenant->logo,
                'email_supported' => $hasTenantEmail,
            ],
            'address' => [
                'address' => $tenant->branding?->address ?? $tenant->address,
                'city' => null,
                'state' => null,
                'postal_code' => null,
                'country' => null,
            ],
            'tax' => [
                'gst_registered' => (bool) ($tenant->taxConfig?->is_gst_enabled ?? false),
                'gstin' => $tenant->taxConfig?->gst_number,
                'pan' => $hasTenantPan ? $tenant->pan_number : null,
                'pan_supported' => $hasTenantPan,
            ],
            'branding' => $tenant->branding,
            'tax_config' => $tenant->taxConfig,
        ];
    }

    private function tenant(Request $request): Tenant
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : $request->user()?->tenant;
        abort_unless($tenant, 404, 'Tenant not found');

        return Tenant::with(['branding', 'taxConfig'])->findOrFail($tenant->id);
    }

    private function storeLogo(Tenant $tenant, Request $request): string
    {
        $imageContent = (string) Image::read($request->file('logo')->getRealPath())
            ->scaleDown(width: 512)
            ->toWebp(82);

        $path = "tenants/{$tenant->id}/logos/{$tenant->slug}-".now()->timestamp.'.webp';

        if (! Storage::disk('public')->put($path, $imageContent)) {
            abort(422, 'Logo could not be stored.');
        }

        return "/storage/{$path}";
    }
}
