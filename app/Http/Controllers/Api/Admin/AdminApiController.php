<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Managers\RoleManager;
use App\Managers\TeamMemberManager;
use App\Models\TeamMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminApiController extends Controller
{
    public function __construct(
        private TeamMemberManager $teamMemberManager,
        private RoleManager $roleManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $teamMembers = $this->teamMemberManager->paginate($perPage);

        return response()->json($teamMembers);
    }

    public function roles(): JsonResponse
    {
        return response()->json($this->roleManager->getAll());
    }

    public function show(TeamMember $teamMember): JsonResponse
    {
        $teamMember->load('roles');
        $roles = $this->roleManager->getAll();

        return response()->json([
            'team_member' => $teamMember,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, TeamMember $teamMember): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:team_members,email,' . $teamMember->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        $roleIds = $validated['role_ids'] ?? [];
        unset($validated['role_ids']);

        $this->teamMemberManager->update($teamMember, $validated);
        $this->teamMemberManager->syncRoles($teamMember, $roleIds);

        return response()->json($teamMember->fresh()->load('roles'));
    }

    public function destroy(TeamMember $teamMember): JsonResponse
    {
        if ($teamMember->id === auth()->id()) {
            return response()->json(['message' => 'You cannot delete yourself.'], 422);
        }

        $this->teamMemberManager->delete($teamMember);

        return response()->json(null, 204);
    }
}
