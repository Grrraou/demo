<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Managers\EmployeeManager;
use App\Managers\RoleManager;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminApiController extends Controller
{
    public function __construct(
        private EmployeeManager $employeeManager,
        private RoleManager $roleManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $employees = $this->employeeManager->paginate($perPage);

        return response()->json($employees);
    }

    public function roles(): JsonResponse
    {
        return response()->json($this->roleManager->getAll());
    }

    public function show(Employee $employee): JsonResponse
    {
        $employee->load('roles');
        $roles = $this->roleManager->getAll();

        return response()->json([
            'employee' => $employee,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:employees,email,' . $employee->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        $roleIds = $validated['role_ids'] ?? [];
        unset($validated['role_ids']);

        $this->employeeManager->update($employee, $validated);
        $this->employeeManager->syncRoles($employee, $roleIds);

        return response()->json($employee->fresh()->load('roles'));
    }

    public function destroy(Employee $employee): JsonResponse
    {
        if ($employee->id === auth()->id()) {
            return response()->json(['message' => 'You cannot delete yourself.'], 422);
        }

        $this->employeeManager->delete($employee);

        return response()->json(null, 204);
    }
}
