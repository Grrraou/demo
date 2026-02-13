<?php

namespace Database\Factories\Sales;

use App\Models\Sales\Invoice;
use App\Models\Sales\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount' => fake()->randomFloat(2, 50, 2000),
            'paid_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'reference' => fake()->optional(0.7)->regexify('[A-Z0-9]{10}'),
            'notes' => fake()->optional(0.2)->sentence(),
        ];
    }
}
