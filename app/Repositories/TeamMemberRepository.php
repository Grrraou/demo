<?php

namespace App\Repositories;

use App\Models\TeamMember;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TeamMemberRepository
{
    public function __construct(
        private TeamMember $model
    ) {}

    public function findByEmail(string $email): ?TeamMember
    {
        return $this->model->newQuery()->where('email', $email)->first();
    }

    public function create(array $data): TeamMember
    {
        return $this->model->newQuery()->create($data);
    }

    public function find(int $id): ?TeamMember
    {
        return $this->model->newQuery()->find($id);
    }

    public function all(): Collection
    {
        return $this->model->newQuery()->orderBy('name')->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()->with('roles')->orderBy('name')->paginate($perPage);
    }

    public function update(TeamMember $teamMember, array $data): bool
    {
        return $teamMember->update($data);
    }

    public function delete(TeamMember $teamMember): bool
    {
        return $teamMember->delete();
    }
}
