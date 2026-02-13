<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\StockLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockLocationFactory extends Factory
{
    protected $model = StockLocation::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'type' => fake()->randomElement(['warehouse', 'shelf', 'bin', 'zone']),
        ];
    }
}
