<?php

namespace App\Http\Controllers\Web\Sales;

use App\Http\Controllers\Controller;
use App\Enums\Sales\OrderStatus;
use App\Models\Inventory\Product;
use App\Models\Sales\SalesOrder;
use App\Managers\Sales\OrderManager;
use App\Repositories\Sales\SalesOrderItemRepository;
use App\Repositories\Sales\SalesOrderRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesOrderController extends Controller
{
    public function __construct(
        private SalesOrderRepository $orderRepository,
        private SalesOrderItemRepository $orderItemRepository,
        private OrderManager $orderManager
    ) {}

    public function index(Request $request): View
    {
        $orders = $this->orderRepository->paginate($request->integer('per_page', 15));

        return view('sales.orders.index', compact('orders'));
    }

    public function create(Request $request): View
    {
        $customers = \App\Models\CustomerCompany::orderBy('name')->get();
        $products = Product::with('unit')->orderBy('name')->get();
        $quoteId = $request->query('quote_id');

        return view('sales.orders.edit', [
            'order' => new SalesOrder,
            'customers' => $customers,
            'products' => $products,
            'quoteId' => $quoteId ? (int) $quoteId : null,
            'isCreate' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $quoteId = $request->input('quote_id');
        if ($quoteId) {
            $quote = \App\Models\Sales\Quote::findOrFail($quoteId);
            $this->orderManager->createFromQuote(
                $quote,
                $request->input('order_date') ?: null
            );
            return redirect()->route('sales.orders.index')->with('success', 'Order created from quote.');
        }

        $validated = $request->validate([
            'customer_company_id' => ['required', 'integer', 'exists:customer_companies,id'],
            'order_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:inventory_products,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $items = array_map(fn ($row) => [
            'product_id' => (int) $row['product_id'],
            'description' => $row['description'] ?? null,
            'quantity' => (float) $row['quantity'],
            'unit_price' => (float) $row['unit_price'],
        ], $validated['items']);

        $this->orderManager->createDirect(
            (int) $validated['customer_company_id'],
            $items,
            $validated['order_date'] ?? null,
            $validated['notes'] ?? null
        );

        return redirect()->route('sales.orders.index')->with('success', 'Order created.');
    }

    public function edit(SalesOrder $order): View
    {
        if ($order->status !== OrderStatus::Draft) {
            $order->load(['customer', 'quote', 'items.product']);
            return view('sales.orders.show', compact('order'));
        }

        $customers = \App\Models\CustomerCompany::orderBy('name')->get();
        $products = Product::with('unit')->orderBy('name')->get();
        $order->load(['customer', 'quote', 'items.product']);

        return view('sales.orders.edit', [
            'order' => $order,
            'customers' => $customers,
            'products' => $products,
            'quoteId' => null,
            'isCreate' => false,
        ]);
    }

    public function update(Request $request, SalesOrder $order): RedirectResponse
    {
        if ($order->status !== OrderStatus::Draft) {
            return redirect()->route('sales.orders.index')->with('error', 'Only draft orders can be edited.');
        }

        $validated = $request->validate([
            'customer_company_id' => ['required', 'integer', 'exists:customer_companies,id'],
            'order_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:inventory_products,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $this->orderRepository->update($order, [
            'customer_company_id' => $validated['customer_company_id'],
            'order_date' => $validated['order_date'] ?? now()->toDateString(),
            'notes' => $validated['notes'] ?? null,
        ]);

        $order->items()->delete();
        foreach ($validated['items'] as $row) {
            $this->orderItemRepository->create([
                'sales_order_id' => $order->id,
                'product_id' => (int) $row['product_id'],
                'description' => $row['description'] ?? null,
                'quantity' => (float) $row['quantity'],
                'unit_price' => (float) $row['unit_price'],
            ]);
        }

        return redirect()->route('sales.orders.index')->with('success', 'Order updated.');
    }

    public function show(SalesOrder $order): View
    {
        $order->load(['customer', 'quote', 'items.product', 'deliveries', 'invoices']);

        return view('sales.orders.show', compact('order'));
    }
}
