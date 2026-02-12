<?php

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository
{
    public function __construct(
        private Role $model
    ) {}

    public function all(): Collection
    {
        return $this->model->newQuery()->orderBy('name')->get();
    }
}
