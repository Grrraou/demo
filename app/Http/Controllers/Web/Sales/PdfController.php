<?php

namespace App\Http\Controllers\Web\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Delivery;
use App\Models\Sales\Invoice;
use App\Models\Sales\Quote;
use App\Models\Sales\SalesOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfController extends Controller
{
    public function quote(Quote $quote): Response
    {
        $quote->load(['customer', 'items.product']);

        $pdf = Pdf::loadView('sales.pdf.quote', compact('quote'));

        return $pdf->download($quote->number . '.pdf');
    }

    public function order(SalesOrder $order): Response
    {
        $order->load(['customer', 'quote', 'items.product']);

        $pdf = Pdf::loadView('sales.pdf.order', compact('order'));

        return $pdf->download($order->number . '.pdf');
    }

    public function delivery(Delivery $delivery): Response
    {
        $delivery->load(['salesOrder.customer', 'items.salesOrderItem.product']);

        $pdf = Pdf::loadView('sales.pdf.delivery', compact('delivery'));

        return $pdf->download($delivery->number . '.pdf');
    }

    public function invoice(Invoice $invoice): Response
    {
        $invoice->load(['customer', 'salesOrder', 'items.product', 'payments']);

        $pdf = Pdf::loadView('sales.pdf.invoice', compact('invoice'));

        return $pdf->download($invoice->number . '.pdf');
    }
}
