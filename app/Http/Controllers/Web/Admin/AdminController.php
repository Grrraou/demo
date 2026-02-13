<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Managers\OwnedCompanyManager;
use App\Managers\RoleManager;
use App\Managers\UserManager;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        private UserManager $userManager,
        private RoleManager $roleManager,
        private OwnedCompanyManager $ownedCompanyManager
    ) {}

    public function index(Request $request): View
    {
        $users = $this->userManager->paginate($request->integer('per_page', 15));

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load('roles', 'ownedCompanies');
        $roles = $this->roleManager->getAll();
        $ownedCompanies = $this->ownedCompanyManager->getAll();

        return view('admin.users.show', compact('user', 'roles', 'ownedCompanies'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'owned_company_ids' => ['nullable', 'array'],
            'owned_company_ids.*' => ['integer', 'exists:owned_companies,id'],
        ]);

        $roleIds = $validated['role_ids'] ?? [];
        $ownedCompanyIds = $validated['owned_company_ids'] ?? [];
        unset($validated['role_ids'], $validated['owned_company_ids']);

        $this->userManager->update($user, $validated);
        $this->userManager->syncRoles($user, $roleIds);
        $this->userManager->syncOwnedCompanies($user, $ownedCompanyIds);

        return redirect()->route('admin.users.show', $user)->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.show', $user)->with('error', 'You cannot delete yourself.');
        }

        $this->userManager->delete($user);

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }
}
