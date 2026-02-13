<?php

namespace App\Managers;

use App\Models\Employee;
use App\Repositories\EmployeeRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthManager
{
    public function __construct(
        private EmployeeRepository $employeeRepository
    ) {}

    public function register(array $data): Employee
    {
        $data['password'] = Hash::make($data['password']);
        return $this->employeeRepository->create($data);
    }

    public function login(array $credentials): array
    {
        $employee = $this->attemptOrFail($credentials);
        $employee->tokens()->delete();
        $token = $employee->createToken('api')->plainTextToken;
        return ['user' => $employee, 'token' => $token];
    }

    public function attemptOrFail(array $credentials): Employee
    {
        $employee = $this->employeeRepository->findByEmail($credentials['email']);
        if (! $employee || ! Hash::check($credentials['password'], $employee->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }
        return $employee;
    }

    public function logout(Employee $employee): void
    {
        $employee->currentAccessToken()->delete();
    }
}
