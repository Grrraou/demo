<?php

namespace App\Managers;

use App\Models\Role;
use App\Repositories\RoleRepository;
use Illuminate\Database\Eloquent\Collection;

class RoleManager
{
    public function __construct(
        private RoleRepository $roleRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->roleRepository->all();
    }
}
