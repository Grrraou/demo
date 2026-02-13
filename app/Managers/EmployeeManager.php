<?php

namespace App\Managers;

use App\Models\Employee;
use App\Repositories\EmployeeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class EmployeeManager
{
    public function __construct(
        private EmployeeRepository $employeeRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->employeeRepository->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->employeeRepository->paginate($perPage);
    }

    public function find(int $id): ?Employee
    {
        return $this->employeeRepository->find($id);
    }

    public function update(Employee $employee, array $data): bool
    {
        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        return $this->employeeRepository->update($employee, $data);
    }

    public function delete(Employee $employee): bool
    {
        return $this->employeeRepository->delete($employee);
    }

    public function syncRoles(Employee $employee, array $roleIds): void
    {
        $employee->roles()->sync($roleIds);
    }

    public function syncOwnedCompanies(Employee $employee, array $ownedCompanyIds): void
    {
        $employee->ownedCompanies()->sync($ownedCompanyIds);
    }
}
