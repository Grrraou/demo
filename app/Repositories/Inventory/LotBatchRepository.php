<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\LotBatch;
use Illuminate\Database\Eloquent\Collection;

class LotBatchRepository
{
    public function __construct(
        private LotBatch $model
    ) {}

    public function find(int $id): ?LotBatch
    {
        return $this->model->newQuery()->find($id);
    }

    public function getByProduct(int $productId): Collection
    {
        return $this->model->newQuery()->where('product_id', $productId)->orderBy('batch_number')->get();
    }

    public function create(array $data): LotBatch
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(LotBatch $lotBatch, array $data): bool
    {
        return $lotBatch->update($data);
    }

    public function delete(LotBatch $lotBatch): bool
    {
        return $lotBatch->delete();
    }
}
