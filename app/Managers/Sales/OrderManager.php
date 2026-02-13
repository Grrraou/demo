<?php

namespace App\Managers\Sales;

use App\Enums\Sales\OrderStatus;
use App\Models\Inventory\Stock;
use App\Models\Sales\Quote;
use App\Models\Sales\SalesOrder;
use App\Managers\Inventory\StockManager as InventoryStockManager;
use App\Repositories\Inventory\StockLocationRepository;
use App\Repositories\Sales\SalesOrderItemRepository;
use App\Repositories\Sales\SalesOrderRepository;

class OrderManager
{
    public function __construct(
        private SalesOrderRepository $orderRepository,
        private SalesOrderItemRepository $orderItemRepository,
        private InventoryStockManager $stockManager,
        private StockLocationRepository $locationRepository
    ) {}

    public function createFromQuote(Quote $quote, ?string $orderDate = null): SalesOrder
    {
        if ($quote->status !== \App\Enums\Sales\QuoteStatus::Accepted) {
            throw new \InvalidArgumentException('Only accepted quotes can be converted to orders.');
        }
        if ($quote->order()->exists()) {
            throw new \InvalidArgumentException('Quote already has an order.');
        }

        $order = $this->orderRepository->create([
            'quote_id' => $quote->id,
            'customer_company_id' => $quote->customer_company_id,
            'number' => $this->orderRepository->nextNumber(),
            'status' => OrderStatus::Draft,
            'order_date' => $orderDate ?? now()->toDateString(),
            'notes' => $quote->notes,
        ]);

        foreach ($quote->items as $item) {
            $this->orderItemRepository->create([
                'sales_order_id' => $order->id,
                'product_id' => $item->product_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ]);
        }

        return $order->load(['items.product', 'customer', 'quote']);
    }

    public function createDirect(int $customerId, array $items, ?string $orderDate = null, ?string $notes = null): SalesOrder
    {
        $order = $this->orderRepository->create([
            'quote_id' => null,
            'customer_company_id' => $customerId,
            'number' => $this->orderRepository->nextNumber(),
            'status' => OrderStatus::Draft,
            'order_date' => $orderDate ?? now()->toDateString(),
            'notes' => $notes,
        ]);

        foreach ($items as $row) {
            $this->orderItemRepository->create([
                'sales_order_id' => $order->id,
                'product_id' => $row['product_id'],
                'description' => $row['description'] ?? null,
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
            ]);
        }

        return $order->load(['items.product', 'customer']);
    }

    /** Reserve stock for order items (first location per product). */
    public function reserveStock(SalesOrder $order, ?int $locationId = null): void
    {
        if ($order->status !== OrderStatus::Draft && $order->status !== OrderStatus::Confirmed) {
            throw new \InvalidArgumentException('Only draft or confirmed orders can reserve stock.');
        }

        $locationId = $locationId ?? $this->locationRepository->all()->first()?->id;
        if (! $locationId) {
            throw new \InvalidArgumentException('No stock location available.');
        }

        foreach ($order->items as $item) {
            $stock = $this->stockManager->getOrCreate($item->product_id, $locationId);
            $this->stockManager->reserve($stock, (float) $item->quantity);
        }

        if ($order->status === OrderStatus::Draft) {
            $order->update(['status' => OrderStatus::Confirmed]);
        }
    }
}
