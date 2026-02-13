<?php

namespace App\Managers\Inventory;

use App\Models\Inventory\Supplier;
use App\Repositories\Inventory\SupplierRepository;
use Illuminate\Database\Eloquent\Collection;

class SupplierManager
{
    public function __construct(
        private SupplierRepository $supplierRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->supplierRepository->all();
    }

    public function find(int $id): ?Supplier
    {
        return $this->supplierRepository->find($id);
    }

    public function create(array $data): Supplier
    {
        return $this->supplierRepository->create($data);
    }

    public function update(Supplier $supplier, array $data): bool
    {
        return $this->supplierRepository->update($supplier, $data);
    }

    public function delete(Supplier $supplier): bool
    {
        return $this->supplierRepository->delete($supplier);
    }
}
