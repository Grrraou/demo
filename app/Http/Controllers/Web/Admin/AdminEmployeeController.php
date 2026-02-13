<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Managers\EmployeeManager;
use App\Managers\OwnedCompanyManager;
use App\Managers\RoleManager;
use App\Models\Employee;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminEmployeeController extends Controller
{
    public function __construct(
        private EmployeeManager $employeeManager,
        private RoleManager $roleManager,
        private OwnedCompanyManager $ownedCompanyManager
    ) {}

    public function index(Request $request): View
    {
        $employees = $this->employeeManager->paginate($request->integer('per_page', 15));

        return view('admin.employees.index', compact('employees'));
    }

    public function show(Employee $employee): View
    {
        $employee->load('roles', 'ownedCompanies');
        $roles = Role::with('permissions')->orderBy('name')->get();
        $ownedCompanies = $this->ownedCompanyManager->getAll();

        return view('admin.employees.show', compact('employee', 'roles', 'ownedCompanies'));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:employees,email,' . $employee->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'owned_company_ids' => ['nullable', 'array'],
            'owned_company_ids.*' => ['integer', 'exists:owned_companies,id'],
        ]);

        $roleIds = $validated['role_ids'] ?? [];
        $ownedCompanyIds = $validated['owned_company_ids'] ?? [];
        unset($validated['role_ids'], $validated['owned_company_ids']);

        $this->employeeManager->update($employee, $validated);
        $this->employeeManager->syncRoles($employee, $roleIds);
        $this->employeeManager->syncOwnedCompanies($employee, $ownedCompanyIds);

        return redirect()->route('admin.employees.show', $employee)->with('success', 'Employee updated.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        if ($employee->id === auth()->id()) {
            return redirect()->route('admin.employees.show', $employee)->with('error', 'You cannot delete yourself.');
        }

        $this->employeeManager->delete($employee);

        return redirect()->route('admin.employees.index')->with('success', 'Employee deleted.');
    }
}
