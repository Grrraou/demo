<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\LotBatch;
use App\Models\Inventory\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class LotBatchFactory extends Factory
{
    protected $model = LotBatch::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'batch_number' => strtoupper(fake()->unique()->bothify('BATCH-####-????')),
            'expiry_date' => fake()->optional(0.7)->dateTimeBetween('+1 month', '+2 years')->format('Y-m-d'),
            'quantity' => fake()->randomFloat(4, 0, 500),
        ];
    }
}
