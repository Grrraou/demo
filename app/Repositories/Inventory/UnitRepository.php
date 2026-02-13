<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\Unit;
use Illuminate\Database\Eloquent\Collection;

class UnitRepository
{
    public function __construct(
        private Unit $model
    ) {}

    public function find(int $id): ?Unit
    {
        return $this->model->newQuery()->find($id);
    }

    public function all(): Collection
    {
        return $this->model->newQuery()->orderBy('symbol')->get();
    }

    public function create(array $data): Unit
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Unit $unit, array $data): bool
    {
        return $unit->update($data);
    }

    public function delete(Unit $unit): bool
    {
        return $unit->delete();
    }
}
