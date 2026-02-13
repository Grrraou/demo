<?php

namespace App\Managers\Inventory;

use App\Models\Inventory\Unit;
use App\Repositories\Inventory\UnitRepository;
use Illuminate\Database\Eloquent\Collection;

class UnitManager
{
    public function __construct(
        private UnitRepository $unitRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->unitRepository->all();
    }

    public function find(int $id): ?Unit
    {
        return $this->unitRepository->find($id);
    }

    public function create(array $data): Unit
    {
        return $this->unitRepository->create($data);
    }

    public function update(Unit $unit, array $data): bool
    {
        return $this->unitRepository->update($unit, $data);
    }

    public function delete(Unit $unit): bool
    {
        return $this->unitRepository->delete($unit);
    }
}
