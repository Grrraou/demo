<?php

namespace Database\Factories\Sales;

use App\Enums\Sales\InvoiceStatus;
use App\Models\CustomerCompany;
use App\Models\Sales\Invoice;
use App\Models\Sales\SalesOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'sales_order_id' => SalesOrder::factory(),
            'customer_company_id' => CustomerCompany::factory(),
            'number' => 'INV-' . fake()->unique()->numerify('######'),
            'status' => InvoiceStatus::Draft,
            'invoice_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
            'notes' => fake()->optional(0.2)->sentence(),
        ];
    }
}
