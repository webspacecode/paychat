<?php

namespace App\Services\ProductImages\Contracts;

interface ProductImageProviderInterface
{
    public function name(): string;

    public function search(string $query, int $limit = 5): array;
}
