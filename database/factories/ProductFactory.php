<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $hargaModal = $this->faker->numberBetween(10000, 100000);

        return [
            'name' => $this->faker->words(3, true),
            'unit' => 'pcs',
            'category_id' => Category::factory(),
            'harga_modal' => $hargaModal,
            'harga_jual' => (int) round($hargaModal * 1.5, -2),
            'harga_marketplace' => (int) round($hargaModal * 1.6, -2),
            'berat' => $this->faker->numberBetween(50, 500),
            'current_stock' => 0,
            'status' => 'active',
        ];
    }
}
