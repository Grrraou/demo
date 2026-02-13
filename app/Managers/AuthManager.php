<?php

namespace App\Managers;

use App\Models\TeamMember;
use App\Repositories\TeamMemberRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthManager
{
    public function __construct(
        private TeamMemberRepository $teamMemberRepository
    ) {}

    public function register(array $data): TeamMember
    {
        $data['password'] = Hash::make($data['password']);
        return $this->teamMemberRepository->create($data);
    }

    public function login(array $credentials): array
    {
        $teamMember = $this->attemptOrFail($credentials);
        $teamMember->tokens()->delete();
        $token = $teamMember->createToken('api')->plainTextToken;
        return ['user' => $teamMember, 'token' => $token];
    }

    public function attemptOrFail(array $credentials): TeamMember
    {
        $teamMember = $this->teamMemberRepository->findByEmail($credentials['email']);
        if (! $teamMember || ! Hash::check($credentials['password'], $teamMember->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }
        return $teamMember;
    }

    public function logout(TeamMember $teamMember): void
    {
        $teamMember->currentAccessToken()->delete();
    }
}
