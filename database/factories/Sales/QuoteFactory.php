<?php

namespace Database\Factories\Sales;

use App\Enums\Sales\QuoteStatus;
use App\Models\CustomerCompany;
use App\Models\Sales\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        return [
            'customer_company_id' => CustomerCompany::factory(),
            'number' => 'Q-' . fake()->unique()->numerify('######'),
            'status' => QuoteStatus::Draft,
            'valid_until' => fake()->dateTimeBetween('+1 week', '+1 month'),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }
}
