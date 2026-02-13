<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Managers\OwnedCompanyManager;
use App\Managers\RoleManager;
use App\Managers\TeamMemberManager;
use App\Models\Role;
use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTeamMemberController extends Controller
{
    public function __construct(
        private TeamMemberManager $teamMemberManager,
        private RoleManager $roleManager,
        private OwnedCompanyManager $ownedCompanyManager
    ) {}

    public function index(Request $request): View
    {
        $teamMembers = $this->teamMemberManager->paginate($request->integer('per_page', 15));

        return view('admin.team-members.index', compact('teamMembers'));
    }

    public function show(TeamMember $teamMember): View
    {
        $teamMember->load('roles', 'ownedCompanies');
        $roles = Role::with('permissions')->orderBy('name')->get();
        $ownedCompanies = $this->ownedCompanyManager->getAll();

        return view('admin.team-members.show', compact('teamMember', 'roles', 'ownedCompanies'));
    }

    public function update(Request $request, TeamMember $teamMember): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:team_members,email,' . $teamMember->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'owned_company_ids' => ['nullable', 'array'],
            'owned_company_ids.*' => ['integer', 'exists:owned_companies,id'],
        ]);

        $roleIds = $validated['role_ids'] ?? [];
        $ownedCompanyIds = $validated['owned_company_ids'] ?? [];
        unset($validated['role_ids'], $validated['owned_company_ids']);

        $this->teamMemberManager->update($teamMember, $validated);
        $this->teamMemberManager->syncRoles($teamMember, $roleIds);
        $this->teamMemberManager->syncOwnedCompanies($teamMember, $ownedCompanyIds);

        return redirect()->route('admin.team-members.show', $teamMember)->with('success', 'Team member updated.');
    }

    public function destroy(TeamMember $teamMember): RedirectResponse
    {
        if ($teamMember->id === auth()->id()) {
            return redirect()->route('admin.team-members.show', $teamMember)->with('error', 'You cannot delete yourself.');
        }

        $this->teamMemberManager->delete($teamMember);

        return redirect()->route('admin.team-members.index')->with('success', 'Team member deleted.');
    }
}
