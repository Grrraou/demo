<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Quote;
use App\Models\Sales\SalesOrder;
use App\Managers\Sales\OrderManager;
use App\Repositories\Sales\SalesOrderRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    public function __construct(
        private SalesOrderRepository $orderRepository,
        private OrderManager $orderManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $orders = $this->orderRepository->paginate($perPage);

        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quote_id' => ['nullable', 'integer', 'exists:sales_quotes,id'],
            'customer_id' => ['required_without:quote_id', 'integer', 'exists:customer_companies,id'],
            'order_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required_without:quote_id', 'array'],
            'items.*.product_id' => ['required', 'integer', 'exists:inventory_products,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            if (! empty($validated['quote_id'])) {
                $quote = Quote::findOrFail($validated['quote_id']);
                $order = $this->orderManager->createFromQuote($quote, $validated['order_date'] ?? null);
            } else {
                $order = $this->orderManager->createDirect(
                    $validated['customer_id'],
                    $validated['items'],
                    $validated['order_date'] ?? null,
                    $validated['notes'] ?? null
                );
            }
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($order, 201);
    }

    public function show(SalesOrder $salesOrder): JsonResponse
    {
        $salesOrder->load(['customer', 'quote', 'items.product', 'deliveries', 'invoices']);

        return response()->json($salesOrder);
    }

    public function reserveStock(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        $validated = $request->validate([
            'location_id' => ['nullable', 'integer', 'exists:inventory_stock_locations,id'],
        ]);

        try {
            $this->orderManager->reserveStock($salesOrder, $validated['location_id'] ?? null);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($salesOrder->fresh()->load('items.product'));
    }
}
