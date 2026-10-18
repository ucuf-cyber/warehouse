<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SaleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'SO-'.strtoupper(Str::random(8)),
            'marketplace' => 'manual',
            'customer_name' => $this->faker->name(),
            'status' => 'completed',
            'sale_date' => now(),
            'total_amount' => 0,
            'shipping_cost' => 0,
            'admin_fee' => 0,
        ];
    }
}
