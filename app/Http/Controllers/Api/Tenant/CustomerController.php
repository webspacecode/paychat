<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'keyword' => ['nullable', 'string', 'max:150'],
            'name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $search = $validated['search'] ?? $validated['keyword'] ?? null;
        $perPage = $validated['per_page'] ?? 20;

        $customers = Customer::query()
            ->select([
                'id',
                'name',
                'email',
                'phone',
                'location_id',
                'customer_type',
                'loyalty_points',
                'created_at',
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($validated['name'] ?? null, fn ($query, $name) =>
                $query->where('name', 'like', "%{$name}%")
            )
            ->when($validated['email'] ?? null, fn ($query, $email) =>
                $query->where('email', 'like', "%{$email}%")
            )
            ->when($validated['phone'] ?? null, fn ($query, $phone) =>
                $query->where('phone', 'like', "%{$phone}%")
            )
            ->latest()
            ->paginate($perPage);

        return response()->json($customers);
    }
}
