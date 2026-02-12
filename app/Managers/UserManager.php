<?php

namespace App\Managers;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserManager
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->userRepository->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage);
    }

    public function find(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function update(User $user, array $data): bool
    {
        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        return $this->userRepository->update($user, $data);
    }

    public function delete(User $user): bool
    {
        return $this->userRepository->delete($user);
    }

    public function syncRoles(User $user, array $roleIds): void
    {
        $user->roles()->sync($roleIds);
    }
}
