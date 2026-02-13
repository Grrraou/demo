<?php

namespace Database\Factories\Sales;

use App\Models\Inventory\Product;
use App\Models\Sales\Invoice;
use App\Models\Sales\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'product_id' => Product::factory(),
            'description' => fake()->optional(0.3)->sentence(),
            'quantity' => fake()->randomFloat(4, 1, 50),
            'unit_price' => fake()->randomFloat(2, 5, 200),
        ];
    }
}
