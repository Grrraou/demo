<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\Stock;
use App\Models\Inventory\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        $quantity = fake()->randomFloat(4, 0.1, 100);
        $type = fake()->randomElement([StockMovement::TYPE_ENTRY, StockMovement::TYPE_EXIT]);

        return [
            'stock_id' => Stock::factory(),
            'type' => $type,
            'quantity' => $type === StockMovement::TYPE_ENTRY ? $quantity : -$quantity,
            'reference_id' => fake()->optional(0.3)->randomNumber(5),
            'note' => fake()->optional(0.5)->sentence(),
        ];
    }
}
