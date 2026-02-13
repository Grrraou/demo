<?php

namespace Database\Factories\Sales;

use App\Models\Inventory\Product;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderItemFactory extends Factory
{
    protected $model = SalesOrderItem::class;

    public function definition(): array
    {
        return [
            'sales_order_id' => SalesOrder::factory(),
            'product_id' => Product::factory(),
            'description' => fake()->optional(0.3)->sentence(),
            'quantity' => fake()->randomFloat(4, 1, 50),
            'unit_price' => fake()->randomFloat(2, 5, 200),
        ];
    }
}
