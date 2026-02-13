<?php

namespace App\Http\Controllers\Web\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Delivery;
use App\Managers\Sales\DeliveryManager;
use App\Repositories\Sales\DeliveryRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function __construct(
        private DeliveryRepository $deliveryRepository,
        private DeliveryManager $deliveryManager
    ) {}

    public function index(Request $request): View
    {
        $deliveries = $this->deliveryRepository->paginate($request->integer('per_page', 15));

        return view('sales.deliveries.index', compact('deliveries'));
    }

    public function create(Request $request): View
    {
        $orderId = $request->query('order_id');
        $order = $orderId ? \App\Models\Sales\SalesOrder::with('items.product')->find($orderId) : null;
        $orders = \App\Models\Sales\SalesOrder::with('customer')->orderByDesc('order_date')->get();

        return view('sales.deliveries.create', [
            'order' => $order,
            'orders' => $orders,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sales_order_id' => ['required', 'integer', 'exists:sales_orders,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sales_order_item_id' => ['required', 'integer', 'exists:sales_order_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
        ]);

        $items = array_map(fn ($row) => [
            'sales_order_item_id' => (int) $row['sales_order_item_id'],
            'quantity' => (float) $row['quantity'],
        ], $validated['items']);

        $order = \App\Models\Sales\SalesOrder::findOrFail($validated['sales_order_id']);
        $this->deliveryManager->create($order, $items, $validated['notes'] ?? null);

        return redirect()->route('sales.deliveries.index')->with('success', 'Delivery created.');
    }

    public function show(Delivery $delivery): View
    {
        $delivery->load(['salesOrder.customer', 'items.salesOrderItem.product']);

        return view('sales.deliveries.show', compact('delivery'));
    }
}
