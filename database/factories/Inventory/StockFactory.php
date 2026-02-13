<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\Product;
use App\Models\Inventory\Stock;
use App\Models\Inventory\StockLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockFactory extends Factory
{
    protected $model = Stock::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'location_id' => StockLocation::factory(),
            'quantity' => fake()->randomFloat(4, 0, 1000),
            'reserved' => 0,
        ];
    }
}
