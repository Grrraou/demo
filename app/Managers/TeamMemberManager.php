<?php

namespace App\Managers;

use App\Models\TeamMember;
use App\Repositories\TeamMemberRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class TeamMemberManager
{
    public function __construct(
        private TeamMemberRepository $teamMemberRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->teamMemberRepository->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->teamMemberRepository->paginate($perPage);
    }

    public function find(int $id): ?TeamMember
    {
        return $this->teamMemberRepository->find($id);
    }

    public function update(TeamMember $teamMember, array $data): bool
    {
        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        return $this->teamMemberRepository->update($teamMember, $data);
    }

    public function delete(TeamMember $teamMember): bool
    {
        return $this->teamMemberRepository->delete($teamMember);
    }

    public function syncRoles(TeamMember $teamMember, array $roleIds): void
    {
        $teamMember->roles()->sync($roleIds);
    }

    public function syncOwnedCompanies(TeamMember $teamMember, array $ownedCompanyIds): void
    {
        $teamMember->ownedCompanies()->sync($ownedCompanyIds);
    }
}
