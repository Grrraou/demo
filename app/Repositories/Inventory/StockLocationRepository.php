<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\StockLocation;
use Illuminate\Database\Eloquent\Collection;

class StockLocationRepository
{
    public function __construct(
        private StockLocation $model
    ) {}

    public function find(int $id): ?StockLocation
    {
        return $this->model->newQuery()->find($id);
    }

    public function all(): Collection
    {
        return $this->model->newQuery()->orderBy('name')->get();
    }

    public function create(array $data): StockLocation
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(StockLocation $location, array $data): bool
    {
        return $location->update($data);
    }

    public function delete(StockLocation $location): bool
    {
        return $location->delete();
    }
}
