<?php

namespace App\Services;

use App\Models\Tenant\Customer;
use Illuminate\Database\Eloquent\Collection;

class CustomerIdentityService
{
    public function normalizePhone(?string $phone): ?string
    {
        $phone = trim((string) $phone);
        if ($phone === '') return null;
        $digits = preg_replace('/\D+/', '', $phone);
        return $digits !== '' ? $digits : $phone;
    }

    public function phoneCandidates(?string $rawPhone, ?string $normalizedPhone = null): array
    {
        $normalizedPhone ??= $this->normalizePhone($rawPhone);
        return collect([$rawPhone, $normalizedPhone])
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->map(fn ($value) => trim($value))
            ->unique()->take(2)->values()->all();
    }

    public function findMatches(array $data): Collection
    {
        $phone = $this->normalizePhone($data['phone'] ?? null);
        $name = trim((string) ($data['name'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        if (! $phone && $name === '' && $email === '') return new Collection();
        return Customer::query()
            ->select(['id', 'name', 'phone', 'email'])
            ->where(function ($query) use ($data, $phone, $name, $email) {
                if ($phone) $query->orWhereIn('phone', $this->phoneCandidates($data['phone'] ?? null, $phone));
                if ($email !== '') $query->orWhere('email', $email);
                if ($name !== '') $query->orWhere('name', 'like', $name.'%');
            })
            ->limit(10)->get();
    }

    public function create(array $data): Customer
    {
        return Customer::create([
            'name' => $data['name'] ?? null,
            'phone' => $this->normalizePhone($data['phone'] ?? null),
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
        ]);
    }

    public function resolveOrCreate(array $data): ?Customer
    {
        if (! empty($data['customer_id'])) {
            return Customer::find($data['customer_id']);
        }
        $phone = $this->normalizePhone($data['phone'] ?? null);
        if (! $phone) return null;
        $customer = Customer::whereIn('phone', $this->phoneCandidates($data['phone'] ?? null, $phone))->first();
        return $customer ?: $this->create($data);
    }
}
