<?php

namespace App\Services;

use App\Models\Transaction;

class PaymentService
{
    public function create(float $amount, string $method, int $userId)
    {
        return Transaction::create([
            'user_id' => $userId,
            'amount' => $amount,
            'method' => $method,
            'status' => 'pending'
        ]);
    }
}
