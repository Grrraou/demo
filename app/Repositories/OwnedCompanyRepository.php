<?php

namespace App\Repositories;

use App\Models\OwnedCompany;
use Illuminate\Database\Eloquent\Collection;

class OwnedCompanyRepository
{
    public function __construct(
        private OwnedCompany $model
    ) {}

    public function find(int $id): ?OwnedCompany
    {
        return $this->model->newQuery()->find($id);
    }

    public function findBySlug(string $slug): ?OwnedCompany
    {
        return $this->model->newQuery()->where('slug', $slug)->first();
    }

    public function all(): Collection
    {
        return $this->model->newQuery()->orderBy('name')->get();
    }

    public function create(array $data): OwnedCompany
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(OwnedCompany $company, array $data): bool
    {
        return $company->update($data);
    }

    public function delete(OwnedCompany $company): bool
    {
        return $company->delete();
    }
}
