<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Managers\RoleManager;
use App\Managers\UserManager;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminApiController extends Controller
{
    public function __construct(
        private UserManager $userManager,
        private RoleManager $roleManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $users = $this->userManager->paginate($perPage);

        return response()->json($users);
    }

    public function roles(): JsonResponse
    {
        return response()->json($this->roleManager->getAll());
    }

    public function show(User $user): JsonResponse
    {
        $user->load('roles');
        $roles = $this->roleManager->getAll();

        return response()->json([
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        $roleIds = $validated['role_ids'] ?? [];
        unset($validated['role_ids']);

        $this->userManager->update($user, $validated);
        $this->userManager->syncRoles($user, $roleIds);

        return response()->json($user->fresh()->load('roles'));
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'You cannot delete yourself.'], 422);
        }

        $this->userManager->delete($user);

        return response()->json(null, 204);
    }
}
