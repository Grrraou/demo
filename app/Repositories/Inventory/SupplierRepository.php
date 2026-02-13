<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\Supplier;
use Illuminate\Database\Eloquent\Collection;

class SupplierRepository
{
    public function __construct(
        private Supplier $model
    ) {}

    public function find(int $id): ?Supplier
    {
        return $this->model->newQuery()->find($id);
    }

    public function all(): Collection
    {
        return $this->model->newQuery()->orderBy('name')->get();
    }

    public function create(array $data): Supplier
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Supplier $supplier, array $data): bool
    {
        return $supplier->update($data);
    }

    public function delete(Supplier $supplier): bool
    {
        return $supplier->delete();
    }
}
