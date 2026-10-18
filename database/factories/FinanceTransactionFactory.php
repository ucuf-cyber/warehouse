<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FinanceTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['income', 'expense']),
            'category' => $this->faker->randomElement(['Penjualan', 'Packing', 'Ongkir', 'Iklan', 'Lain-lain']),
            'amount' => $this->faker->numberBetween(10000, 500000),
            'transaction_date' => now(),
            'description' => $this->faker->sentence(4),
        ];
    }
}
