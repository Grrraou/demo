<?php

namespace Database\Factories\Sales;

use App\Enums\Sales\DeliveryStatus;
use App\Models\Sales\Delivery;
use App\Models\Sales\SalesOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryFactory extends Factory
{
    protected $model = Delivery::class;

    public function definition(): array
    {
        return [
            'sales_order_id' => SalesOrder::factory(),
            'number' => 'DLV-' . fake()->unique()->numerify('######'),
            'status' => DeliveryStatus::Pending,
            'delivered_at' => null,
            'notes' => fake()->optional(0.2)->sentence(),
        ];
    }
}
