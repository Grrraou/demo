<?php

namespace App\Http\Controllers\Web\Sales;

use App\Http\Controllers\Controller;
use App\Enums\Sales\InvoiceStatus;
use App\Models\Inventory\Product;
use App\Models\Sales\Invoice;
use App\Managers\Sales\InvoiceManager;
use App\Repositories\Sales\InvoiceRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceRepository $invoiceRepository,
        private InvoiceManager $invoiceManager
    ) {}

    public function index(Request $request): View
    {
        $invoices = $this->invoiceRepository->paginate($request->integer('per_page', 15));

        return view('sales.invoices.index', compact('invoices'));
    }

    public function create(Request $request): View
    {
        $orderId = $request->query('order_id');
        $order = $orderId ? \App\Models\Sales\SalesOrder::with(['customer', 'items.product'])->find($orderId) : null;
        $orders = \App\Models\Sales\SalesOrder::with('customer')->orderByDesc('order_date')->get();

        return view('sales.invoices.create', [
            'order' => $order,
            'orders' => $orders,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sales_order_id' => ['required', 'integer', 'exists:sales_orders,id'],
            'invoice_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $order = \App\Models\Sales\SalesOrder::findOrFail($validated['sales_order_id']);
        $this->invoiceManager->createFromOrder(
            $order,
            $validated['invoice_date'] ?? null,
            $validated['due_date'] ?? null,
            $validated['notes'] ?? null
        );

        return redirect()->route('sales.invoices.index')->with('success', 'Invoice created.');
    }

    public function edit(Invoice $invoice): View
    {
        if ($invoice->status !== InvoiceStatus::Draft) {
            $invoice->load(['customer', 'salesOrder', 'items.product', 'payments']);
            return view('sales.invoices.show', compact('invoice'));
        }

        $customers = \App\Models\CustomerCompany::orderBy('name')->get();
        $products = Product::with('unit')->orderBy('name')->get();
        $invoice->load(['customer', 'salesOrder', 'items.product', 'payments']);

        return view('sales.invoices.edit', [
            'invoice' => $invoice,
            'customers' => $customers,
            'products' => $products,
            'isCreate' => false,
        ]);
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        if ($invoice->status !== InvoiceStatus::Draft) {
            return redirect()->route('sales.invoices.index')->with('error', 'Only draft invoices can be edited.');
        }

        $validated = $request->validate([
            'customer_company_id' => ['required', 'integer', 'exists:customer_companies,id'],
            'invoice_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:inventory_products,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $invoice->update([
            'customer_company_id' => $validated['customer_company_id'],
            'invoice_date' => $validated['invoice_date'] ?? now()->toDateString(),
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $invoice->items()->delete();
        foreach ($validated['items'] as $row) {
            $invoice->items()->create([
                'product_id' => (int) $row['product_id'],
                'description' => $row['description'] ?? null,
                'quantity' => (float) $row['quantity'],
                'unit_price' => (float) $row['unit_price'],
            ]);
        }

        return redirect()->route('sales.invoices.index')->with('success', 'Invoice updated.');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['customer', 'salesOrder', 'items.product', 'payments']);

        return view('sales.invoices.show', compact('invoice'));
    }
}
