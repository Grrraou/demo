<?php

namespace App\Managers;

use App\Models\OwnedCompany;
use App\Repositories\OwnedCompanyRepository;
use Illuminate\Database\Eloquent\Collection;

class OwnedCompanyManager
{
    public function __construct(
        private OwnedCompanyRepository $ownedCompanyRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->ownedCompanyRepository->all();
    }

    public function find(int $id): ?OwnedCompany
    {
        return $this->ownedCompanyRepository->find($id);
    }

    public function update(OwnedCompany $company, array $data): bool
    {
        return $this->ownedCompanyRepository->update($company, $data);
    }

    public function delete(OwnedCompany $company): bool
    {
        return $this->ownedCompanyRepository->delete($company);
    }
}
