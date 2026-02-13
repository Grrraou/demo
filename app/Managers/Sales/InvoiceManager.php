<?php

namespace App\Managers\Sales;

use App\Enums\Sales\InvoiceStatus;
use App\Models\Sales\Invoice;
use App\Models\Sales\SalesOrder;
use App\Repositories\Sales\InvoiceRepository;
use App\Repositories\Sales\PaymentRepository;

class InvoiceManager
{
    public function __construct(
        private InvoiceRepository $invoiceRepository,
        private PaymentRepository $paymentRepository
    ) {}

    public function createFromOrder(SalesOrder $order, ?string $invoiceDate = null, ?string $dueDate = null, ?string $notes = null): Invoice
    {
        $invoice = $this->invoiceRepository->create([
            'sales_order_id' => $order->id,
            'customer_company_id' => $order->customer_company_id,
            'number' => $this->invoiceRepository->nextNumber(),
            'status' => InvoiceStatus::Draft,
            'invoice_date' => $invoiceDate ?? now()->toDateString(),
            'due_date' => $dueDate,
            'notes' => $notes ?? $order->notes,
        ]);

        foreach ($order->items as $item) {
            $invoice->items()->create([
                'product_id' => $item->product_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ]);
        }

        return $invoice->load(['items.product', 'customer', 'salesOrder']);
    }

    public function markSent(Invoice $invoice): Invoice
    {
        if ($invoice->status !== InvoiceStatus::Draft) {
            throw new \InvalidArgumentException('Only draft invoices can be marked sent.');
        }
        $invoice->update(['status' => InvoiceStatus::Sent]);

        return $invoice->fresh();
    }

    public function markPaid(Invoice $invoice, float $amount, ?string $paidAt = null, ?string $reference = null, ?string $notes = null): Invoice
    {
        $paidAt = $paidAt ?? now();
        $this->paymentRepository->create([
            'invoice_id' => $invoice->id,
            'amount' => $amount,
            'paid_at' => $paidAt,
            'reference' => $reference,
            'notes' => $notes,
        ]);

        $invoice->load('payments');
        $paidTotal = (float) $invoice->payments->sum('amount');
        $total = (float) $invoice->items->sum(fn ($i) => (float) $i->quantity * (float) $i->unit_price);
        $invoice->update([
            'status' => $paidTotal >= $total ? InvoiceStatus::Paid : InvoiceStatus::Sent,
        ]);

        return $invoice->fresh()->load('payments');
    }
}
