<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'prefix' => strtoupper($this->faker->unique()->lexify('???')),
            'icon' => 'bi-box',
            'is_active' => true,
        ];
    }
}
