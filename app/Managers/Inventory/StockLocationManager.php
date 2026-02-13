<?php

namespace App\Managers\Inventory;

use App\Models\Inventory\StockLocation;
use App\Repositories\Inventory\StockLocationRepository;
use Illuminate\Database\Eloquent\Collection;

class StockLocationManager
{
    public function __construct(
        private StockLocationRepository $locationRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->locationRepository->all();
    }

    public function find(int $id): ?StockLocation
    {
        return $this->locationRepository->find($id);
    }

    public function create(array $data): StockLocation
    {
        return $this->locationRepository->create($data);
    }

    public function update(StockLocation $location, array $data): bool
    {
        return $this->locationRepository->update($location, $data);
    }

    public function delete(StockLocation $location): bool
    {
        return $this->locationRepository->delete($location);
    }
}
