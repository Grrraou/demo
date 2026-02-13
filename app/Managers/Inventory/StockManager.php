<?php

namespace App\Managers\Inventory;

use App\Models\Inventory\Stock;
use App\Models\Inventory\StockMovement;
use App\Repositories\Inventory\StockMovementRepository;
use App\Repositories\Inventory\StockRepository;
use Illuminate\Database\Eloquent\Collection;

class StockManager
{
    public function __construct(
        private StockRepository $stockRepository,
        private StockMovementRepository $movementRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->stockRepository->all();
    }

    public function find(int $id): ?Stock
    {
        return $this->stockRepository->find($id);
    }

    public function getOrCreate(int $productId, int $locationId): Stock
    {
        return $this->stockRepository->getOrCreate($productId, $locationId);
    }

    public function recordEntry(int $stockId, float $quantity, ?int $referenceId = null, ?string $note = null): StockMovement
    {
        $stock = $this->stockRepository->find($stockId);
        if (! $stock) {
            throw new \InvalidArgumentException("Stock not found: {$stockId}");
        }
        $quantity = abs($quantity);
        $movement = $this->movementRepository->create([
            'stock_id' => $stockId,
            'type' => StockMovement::TYPE_ENTRY,
            'quantity' => $quantity,
            'reference_id' => $referenceId,
            'note' => $note,
        ]);
        $stock->increment('quantity', $quantity);
        return $movement;
    }

    public function recordExit(int $stockId, float $quantity, ?int $referenceId = null, ?string $note = null): StockMovement
    {
        $stock = $this->stockRepository->find($stockId);
        if (! $stock) {
            throw new \InvalidArgumentException("Stock not found: {$stockId}");
        }
        $quantity = abs($quantity);
        $available = (float) $stock->quantity - (float) $stock->reserved;
        if ($quantity > $available) {
            throw new \InvalidArgumentException("Insufficient available quantity. Available: {$available}");
        }
        $movement = $this->movementRepository->create([
            'stock_id' => $stockId,
            'type' => StockMovement::TYPE_EXIT,
            'quantity' => -$quantity,
            'reference_id' => $referenceId,
            'note' => $note,
        ]);
        $stock->decrement('quantity', $quantity);
        return $movement;
    }

    public function recordTransfer(int $fromStockId, int $toStockId, float $quantity, ?string $note = null): array
    {
        $from = $this->stockRepository->find($fromStockId);
        $to = $this->stockRepository->find($toStockId);
        if (! $from || ! $to) {
            throw new \InvalidArgumentException('Source or destination stock not found.');
        }
        $quantity = abs($quantity);
        $available = (float) $from->quantity - (float) $from->reserved;
        if ($quantity > $available) {
            throw new \InvalidArgumentException("Insufficient available quantity. Available: {$available}");
        }
        $exit = $this->movementRepository->create([
            'stock_id' => $fromStockId,
            'type' => StockMovement::TYPE_TRANSFER,
            'quantity' => -$quantity,
            'reference_id' => $toStockId,
            'note' => $note,
        ]);
        $entry = $this->movementRepository->create([
            'stock_id' => $toStockId,
            'type' => StockMovement::TYPE_TRANSFER,
            'quantity' => $quantity,
            'reference_id' => $fromStockId,
            'note' => $note,
        ]);
        $from->decrement('quantity', $quantity);
        $to->increment('quantity', $quantity);
        return ['exit' => $exit, 'entry' => $entry];
    }

    public function getMovements(int $stockId, int $limit = 50): Collection
    {
        return $this->movementRepository->getByStock($stockId, $limit);
    }

    public function reserve(Stock $stock, float $qty): void
    {
        $available = (float) $stock->quantity - (float) $stock->reserved;
        if ($qty > $available) {
            throw new \InvalidArgumentException("Insufficient available quantity. Available: {$available}");
        }
        $stock->increment('reserved', $qty);
    }

    public function releaseReservation(Stock $stock, float $qty): void
    {
        $stock->decrement('reserved', min($qty, (float) $stock->reserved));
    }
}
