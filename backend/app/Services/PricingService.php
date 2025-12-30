<?php

namespace App\Services;

class PricingService
{
    public function calculate(float $weight, float $pricePerKg): float
    {
        return round($weight * $pricePerKg, 2);
    }
}
