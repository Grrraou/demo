<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StockMovementRepository
{
    public function __construct(
        private StockMovement $model
    ) {}

    public function find(int $id): ?StockMovement
    {
        return $this->model->newQuery()->find($id);
    }

    public function create(array $data): StockMovement
    {
        return $this->model->newQuery()->create($data);
    }

    public function getByStock(int $stockId, int $limit = 50): Collection
    {
        return $this->model->newQuery()
            ->where('stock_id', $stockId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()->with('stock.product')->orderByDesc('created_at')->paginate($perPage);
    }
}
