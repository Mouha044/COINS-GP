<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\Package;

class MatchingService
{
    public function match(Package $package)
    {
        return Trip::where('destination', $package->destination)
            ->where('available_weight', '>=', $package->weight)
            ->orderBy('price_per_kg')
            ->first();
    }
}
