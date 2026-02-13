<?php

namespace App\Managers\Sales;

use App\Enums\Sales\DeliveryStatus;
use App\Models\Sales\Delivery;
use App\Models\Sales\SalesOrder;
use App\Repositories\Sales\DeliveryRepository;

class DeliveryManager
{
    public function __construct(
        private DeliveryRepository $deliveryRepository
    ) {}

    public function create(SalesOrder $order, array $items, ?string $notes = null): Delivery
    {
        $delivery = $this->deliveryRepository->create([
            'sales_order_id' => $order->id,
            'number' => $this->deliveryRepository->nextNumber(),
            'status' => DeliveryStatus::Pending,
            'notes' => $notes,
        ]);

        foreach ($items as $row) {
            $delivery->items()->create([
                'sales_order_item_id' => $row['sales_order_item_id'],
                'quantity' => $row['quantity'],
            ]);
        }

        return $delivery->load(['items.salesOrderItem.product']);
    }

    public function markDelivered(Delivery $delivery): Delivery
    {
        $delivery->update([
            'status' => DeliveryStatus::Delivered,
            'delivered_at' => now(),
        ]);

        return $delivery->fresh();
    }
}
