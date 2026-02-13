<?php

namespace App\Managers\Inventory;

use App\Models\Inventory\LotBatch;
use App\Repositories\Inventory\LotBatchRepository;
use Illuminate\Database\Eloquent\Collection;

class LotBatchManager
{
    public function __construct(
        private LotBatchRepository $lotBatchRepository
    ) {}

    public function find(int $id): ?LotBatch
    {
        return $this->lotBatchRepository->find($id);
    }

    public function getByProduct(int $productId): Collection
    {
        return $this->lotBatchRepository->getByProduct($productId);
    }

    public function create(array $data): LotBatch
    {
        return $this->lotBatchRepository->create($data);
    }

    public function update(LotBatch $lotBatch, array $data): bool
    {
        return $this->lotBatchRepository->update($lotBatch, $data);
    }

    public function delete(LotBatch $lotBatch): bool
    {
        return $this->lotBatchRepository->delete($lotBatch);
    }
}
