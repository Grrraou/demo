<?php

namespace App\Managers\Inventory;

use App\Models\Inventory\StockMovement;
use App\Repositories\Inventory\StockMovementRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StockMovementManager
{
    public function __construct(
        private StockMovementRepository $movementRepository
    ) {}

    public function find(int $id): ?StockMovement
    {
        return $this->movementRepository->find($id);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->movementRepository->paginate($perPage);
    }
}
