<?php

namespace Database\Factories\Sales;

use App\Models\Inventory\Product;
use App\Models\Sales\Quote;
use App\Models\Sales\QuoteItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteItemFactory extends Factory
{
    protected $model = QuoteItem::class;

    public function definition(): array
    {
        return [
            'quote_id' => Quote::factory(),
            'product_id' => Product::factory(),
            'description' => fake()->optional(0.3)->sentence(),
            'quantity' => fake()->randomFloat(4, 1, 100),
            'unit_price' => fake()->randomFloat(2, 5, 500),
        ];
    }
}
