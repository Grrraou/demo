<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CustomerRepository
{
    public function __construct(
        private Customer $model
    ) {}

    public function all(): Collection
    {
        return $this->model->newQuery()->orderBy('name')->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()->orderBy('name')->paginate($perPage);
    }

    public function find(int $id): ?Customer
    {
        return $this->model->newQuery()->find($id);
    }

    public function create(array $data): Customer
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Customer $customer, array $data): bool
    {
        return $customer->update($data);
    }

    public function delete(Customer $customer): bool
    {
        return $customer->delete();
    }
}
