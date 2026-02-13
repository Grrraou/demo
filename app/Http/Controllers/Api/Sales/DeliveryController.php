<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Delivery;
use App\Managers\Sales\DeliveryManager;
use App\Repositories\Sales\DeliveryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function __construct(
        private DeliveryRepository $deliveryRepository,
        private DeliveryManager $deliveryManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $deliveries = $this->deliveryRepository->paginate($perPage);

        return response()->json($deliveries);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sales_order_id' => ['required', 'integer', 'exists:sales_orders,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sales_order_item_id' => ['required', 'integer', 'exists:sales_order_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
        ]);

        $order = \App\Models\Sales\SalesOrder::findOrFail($validated['sales_order_id']);

        try {
            $delivery = $this->deliveryManager->create($order, $validated['items'], $validated['notes'] ?? null);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($delivery, 201);
    }

    public function show(Delivery $delivery): JsonResponse
    {
        $delivery->load(['salesOrder', 'items.salesOrderItem.product']);

        return response()->json($delivery);
    }

    public function markDelivered(Delivery $delivery): JsonResponse
    {
        try {
            $delivery = $this->deliveryManager->markDelivered($delivery);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($delivery);
    }
}
