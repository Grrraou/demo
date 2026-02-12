<?php

namespace App\Managers;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthManager
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        return $this->userRepository->create($data);
    }

    public function login(array $credentials): array
    {
        $user = $this->attemptOrFail($credentials);
        $user->tokens()->delete();
        $token = $user->createToken('api')->plainTextToken;
        return ['user' => $user, 'token' => $token];
    }

    public function attemptOrFail(array $credentials): User
    {
        $user = $this->userRepository->findByEmail($credentials['email']);
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }
        return $user;
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
