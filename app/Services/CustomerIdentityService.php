<?php

namespace App\Services;

use App\Models\Tenant\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

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
        $payload = [
            'name' => $data['name'] ?? null,
            'phone' => $this->normalizePhone($data['phone'] ?? null),
            'email' => $data['email'] ?? null,
        ];

        if (Schema::hasColumn((new Customer())->getTable(), 'address')) {
            $payload['address'] = $data['address'] ?? null;
        }

        return Customer::create($payload);
    }

    public function resolveOrCreate(array $data): ?Customer
    {
        if (! empty($data['customer_id'])) {
            return Customer::find($data['customer_id']);
        }

        $phone = $this->normalizePhone($data['phone'] ?? null);
        if (! $phone) return null;

        $customer = Customer::whereIn('phone', $this->phoneCandidates($data['phone'] ?? null, $phone))->first();
        if ($customer) {
            return $this->fillMissingIdentity($customer, $data);
        }

        try {
            return $this->create($data);
        } catch (QueryException $exception) {
            $customer = Customer::whereIn('phone', $this->phoneCandidates($data['phone'] ?? null, $phone))->first();

            if ($customer) {
                return $this->fillMissingIdentity($customer, $data);
            }

            throw $exception;
        }
    }

    private function fillMissingIdentity(Customer $customer, array $data): Customer
    {
        $updates = [];

        foreach (['name', 'email', 'address'] as $field) {
            if (! Schema::hasColumn($customer->getTable(), $field)) {
                continue;
            }

            $value = $data[$field] ?? null;

            if (! $customer->{$field} && is_string($value) && trim($value) !== '') {
                $updates[$field] = trim($value);
            }
        }

        if ($updates) {
            $customer->forceFill($updates)->save();
        }

        return $customer;
    }
}
