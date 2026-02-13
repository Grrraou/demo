<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\Stock;
use Illuminate\Database\Eloquent\Collection;

class StockRepository
{
    public function __construct(
        private Stock $model
    ) {}

    public function find(int $id): ?Stock
    {
        return $this->model->newQuery()->find($id);
    }

    public function getByProductAndLocation(int $productId, int $locationId): ?Stock
    {
        return $this->model->newQuery()
            ->where('product_id', $productId)
            ->where('location_id', $locationId)
            ->first();
    }

    public function getOrCreate(int $productId, int $locationId): Stock
    {
        $stock = $this->getByProductAndLocation($productId, $locationId);
        if ($stock) {
            return $stock;
        }
        return $this->model->newQuery()->create([
            'product_id' => $productId,
            'location_id' => $locationId,
            'quantity' => 0,
            'reserved' => 0,
        ]);
    }

    public function all(): Collection
    {
        return $this->model->newQuery()->with(['product', 'location'])->get();
    }

    public function create(array $data): Stock
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Stock $stock, array $data): bool
    {
        return $stock->update($data);
    }
}
