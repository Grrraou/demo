<?php

namespace App\Managers;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CustomerManager
{
    public function __construct(
        private CustomerRepository $customerRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->customerRepository->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->customerRepository->paginate($perPage);
    }

    public function find(int $id): ?Customer
    {
        return $this->customerRepository->find($id);
    }

    public function create(array $data): Customer
    {
        return $this->customerRepository->create($data);
    }

    public function update(Customer $customer, array $data): bool
    {
        return $this->customerRepository->update($customer, $data);
    }

    public function delete(Customer $customer): bool
    {
        return $this->customerRepository->delete($customer);
    }
}
