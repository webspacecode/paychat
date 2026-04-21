<?php

namespace App\Services\Payments;

class TaxService
{
    protected float $gstPercent;
    protected float $serviceChargePercent;

    public function __construct(float $gstPercent = 18, float $serviceChargePercent = 0)
    {
        $this->gstPercent = $gstPercent;
        $this->serviceChargePercent = $serviceChargePercent;
    }

    public function calculateItemTax(float $price, int $quantity): float
    {
        $lineTotal = $price * $quantity;
        $gst = ($lineTotal * $this->gstPercent)/100;
        $serviceCharge = ($lineTotal * $this->serviceChargePercent)/100;
        return round($gst + $serviceCharge,2);
    }

    public function calculateOrderTax(array $items): float
    {
        $total = 0;
        foreach($items as $item){
            $total += $this->calculateItemTax($item['price'],$item['quantity']);
        }
        return round($total,2);
    }

    public function calculateTotal(float $subtotal, float $discount, float $tax): float
    {
        return round($subtotal + $tax - $discount,2);
    }
}
