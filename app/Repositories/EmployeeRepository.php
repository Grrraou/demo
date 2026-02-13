<?php

namespace App\Repositories;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EmployeeRepository
{
    public function __construct(
        private Employee $model
    ) {}

    public function findByEmail(string $email): ?Employee
    {
        return $this->model->newQuery()->where('email', $email)->first();
    }

    public function create(array $data): Employee
    {
        return $this->model->newQuery()->create($data);
    }

    public function find(int $id): ?Employee
    {
        return $this->model->newQuery()->find($id);
    }

    public function all(): Collection
    {
        return $this->model->newQuery()->orderBy('name')->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()->with('roles')->orderBy('name')->paginate($perPage);
    }

    public function update(Employee $employee, array $data): bool
    {
        return $employee->update($data);
    }

    public function delete(Employee $employee): bool
    {
        return $employee->delete();
    }
}
