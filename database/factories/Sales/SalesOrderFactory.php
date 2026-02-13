<?php

namespace Database\Factories\Sales;

use App\Enums\Sales\OrderStatus;
use App\Models\CustomerCompany;
use App\Models\Sales\SalesOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderFactory extends Factory
{
    protected $model = SalesOrder::class;

    public function definition(): array
    {
        return [
            'quote_id' => null,
            'customer_company_id' => CustomerCompany::factory(),
            'number' => 'SO-' . fake()->unique()->numerify('######'),
            'status' => OrderStatus::Draft,
            'order_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }
}
