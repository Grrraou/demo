<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Invoice;
use App\Models\Sales\SalesOrder;
use App\Managers\Sales\InvoiceManager;
use App\Repositories\Sales\InvoiceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceRepository $invoiceRepository,
        private InvoiceManager $invoiceManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $invoices = $this->invoiceRepository->paginate($perPage);

        return response()->json($invoices);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sales_order_id' => ['required', 'integer', 'exists:sales_orders,id'],
            'invoice_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $order = SalesOrder::findOrFail($validated['sales_order_id']);

        try {
            $invoice = $this->invoiceManager->createFromOrder(
                $order,
                $validated['invoice_date'] ?? null,
                $validated['due_date'] ?? null,
                $validated['notes'] ?? null
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($invoice, 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $invoice->load(['customer', 'salesOrder', 'items.product', 'payments']);

        return response()->json($invoice);
    }

    public function markSent(Invoice $invoice): JsonResponse
    {
        try {
            $invoice = $this->invoiceManager->markSent($invoice);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($invoice);
    }

    public function markPaid(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['nullable', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $invoice = $this->invoiceManager->markPaid(
                $invoice,
                (float) $validated['amount'],
                isset($validated['paid_at']) ? $validated['paid_at'] : null,
                $validated['reference'] ?? null,
                $validated['notes'] ?? null
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($invoice);
    }
}
