<?php

namespace Database\Factories;

use App\Models\DamageCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class DamageCategoryFactory extends Factory
{
    protected $model = DamageCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['LCD Retak', 'Batre Boros', 'Touch ID Rusak', ' Kamera Rusak']),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
