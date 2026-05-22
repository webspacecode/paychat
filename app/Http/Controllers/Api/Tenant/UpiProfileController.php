<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\UpiProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UpiProfileController extends Controller
{
    public function index(string $tenantSlug, Request $request)
    {
        $includeInactive = $request->boolean('include_inactive');
        $includeGlobal = $request->boolean('include_global', true);
        $locationId = $request->input('location_id');

        $profiles = UpiProfile::query()
            ->when(! $includeInactive, fn ($query) => $query->where('is_active', true))
            ->when($locationId !== null, function ($query) use ($locationId, $includeGlobal) {
                $query->where(function ($q) use ($locationId, $includeGlobal) {
                    $q->where('location_id', $locationId);

                    if ($includeGlobal) {
                        $q->orWhereNull('location_id');
                    }
                });
            })
            ->orderBy('sort_order')
            ->orderByDesc('is_default')
            ->orderBy('label')
            ->get();

        return response()->json([
            'data' => $profiles->map(fn (UpiProfile $profile) => $this->profilePayload($profile, true))->values(),
        ]);
    }

    public function store(string $tenantSlug, Request $request)
    {
        $validated = $request->validate($this->rules());

        return DB::transaction(function () use ($validated) {
            $profile = UpiProfile::create($validated);

            if ($profile->is_default) {
                $this->setAsDefault($profile);
                $profile = $profile->fresh();
            }

            return response()->json([
                'data' => $this->profilePayload($profile, true),
            ], 201);
        });
    }

    public function update(string $tenantSlug, Request $request, UpiProfile $profile)
    {
        $validated = $request->validate($this->rules(false));

        return DB::transaction(function () use ($profile, $validated) {
            if (array_key_exists('is_active', $validated) && ! $validated['is_active']) {
                $validated['is_default'] = false;
            }

            $profile->update($validated);

            if (array_key_exists('is_default', $validated) && $profile->is_default) {
                $this->setAsDefault($profile);
                $profile = $profile->fresh();
            }

            return response()->json([
                'data' => $this->profilePayload($profile, true),
            ]);
        });
    }

    public function destroy(string $tenantSlug, UpiProfile $profile)
    {
        $profile->update([
            'is_active' => false,
            'is_default' => false,
        ]);

        return response()->json([
            'data' => $this->profilePayload($profile->fresh(), true),
        ]);
    }

    public function makeDefault(string $tenantSlug, UpiProfile $profile)
    {
        $this->setAsDefault($profile);

        return response()->json([
            'data' => $this->profilePayload($profile->fresh(), true),
        ]);
    }

    private function setAsDefault(UpiProfile $profile): void
    {
        if (! $profile->is_active) {
            abort(422, 'Inactive UPI profile cannot be set as default');
        }

        UpiProfile::query()
            ->whereKeyNot($profile->id)
            ->when(
                $profile->location_id === null,
                fn ($query) => $query->whereNull('location_id'),
                fn ($query) => $query->where('location_id', $profile->location_id)
            )
            ->update(['is_default' => false]);

        $profile->update(['is_default' => true]);
    }

    private function rules(bool $creating = true): array
    {
        $required = $creating ? 'required' : 'sometimes|required';

        return [
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'label' => [$required, 'string', 'max:100'],
            'upi_id' => [$required, 'string', 'max:100', 'regex:/^[A-Za-z0-9._-]+@[A-Za-z0-9._-]+$/'],
            'payee_name' => ['nullable', 'string', 'max:100'],
            'is_default' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function profilePayload(UpiProfile $profile, bool $includeFullUpiId = false): array
    {
        return [
            'id' => $profile->id,
            'location_id' => $profile->location_id,
            'label' => $profile->label,
            'upi_id' => $includeFullUpiId ? $profile->upi_id : null,
            'upi_id_masked' => $this->maskUpiId($profile->upi_id),
            'payee_name' => $profile->payee_name,
            'is_default' => $profile->is_default,
            'is_active' => $profile->is_active,
            'sort_order' => $profile->sort_order,
            'notes' => $profile->notes,
        ];
    }

    private function maskUpiId(?string $upiId): ?string
    {
        if (! $upiId || ! str_contains($upiId, '@')) {
            return $upiId;
        }

        [$name, $handle] = explode('@', $upiId, 2);
        $prefix = substr($name, 0, min(3, strlen($name)));

        return $prefix.'****@'.$handle;
    }
}
