<?php

namespace Database\Seeders;

use App\Enums\Sales\QuoteStatus;
use App\Models\CustomerCompany;
use App\Models\Inventory\Product;
use App\Models\Sales\Invoice;
use App\Models\Sales\Payment;
use App\Models\Sales\Quote;
use App\Models\Sales\QuoteItem;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            CustomerCompany::firstOrCreate(
                ['email' => 'procurement@acmebuyer.test'],
                ['name' => 'Acme Buyer Ltd', 'phone' => '+1-555-1000', 'address' => '100 Commerce St']
            ),
            CustomerCompany::firstOrCreate(
                ['email' => 'orders@globalretail.test'],
                ['name' => 'Global Retail Inc', 'phone' => null, 'address' => null]
            ),
        ];

        $products = Product::orderBy('id')->limit(3)->get();
        if ($products->isEmpty()) {
            return;
        }

        $quote = Quote::create([
            'customer_company_id' => $customers[0]->id,
            'number' => 'Q-000001',
            'status' => QuoteStatus::Accepted,
            'valid_until' => now()->addDays(30),
            'notes' => 'Demo quote',
        ]);
        foreach ($products as $i => $product) {
            QuoteItem::create([
                'quote_id' => $quote->id,
                'product_id' => $product->id,
                'description' => null,
                'quantity' => 10 + $i * 5,
                'unit_price' => 25.50 + $i * 10,
            ]);
        }

        $order = SalesOrder::create([
            'quote_id' => $quote->id,
            'customer_company_id' => $customers[0]->id,
            'number' => 'SO-000001',
            'status' => \App\Enums\Sales\OrderStatus::Confirmed,
            'order_date' => now(),
            'notes' => null,
        ]);
        foreach ($quote->items as $item) {
            SalesOrderItem::create([
                'sales_order_id' => $order->id,
                'product_id' => $item->product_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ]);
        }

        $invoice = Invoice::create([
            'sales_order_id' => $order->id,
            'customer_company_id' => $order->customer_company_id,
            'number' => 'INV-000001',
            'status' => \App\Enums\Sales\InvoiceStatus::Sent,
            'invoice_date' => now(),
            'due_date' => now()->addDays(14),
            'notes' => null,
        ]);
        foreach ($order->items as $item) {
            $invoice->items()->create([
                'product_id' => $item->product_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ]);
        }

        Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => (float) $invoice->items->sum(fn ($i) => (float) $i->quantity * (float) $i->unit_price),
            'paid_at' => now(),
            'reference' => 'DEMO-PAY-001',
            'notes' => null,
        ]);
        $invoice->update(['status' => \App\Enums\Sales\InvoiceStatus::Paid]);
    }
}
