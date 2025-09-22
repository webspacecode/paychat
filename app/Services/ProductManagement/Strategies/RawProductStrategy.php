<?php 

namespace App\Services\ProductManagement\Strategies;

use App\Models\Tenant\Product;

class RawProductStrategy extends DefaultProductStrategy
{
    public function create(array $data): Product
    {
        $data['type'] = 'raw';
        return parent::create($data);
    }
}
