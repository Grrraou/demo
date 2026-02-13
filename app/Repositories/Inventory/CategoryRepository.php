<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository
{
    public function __construct(
        private Category $model
    ) {}

    public function find(int $id): ?Category
    {
        return $this->model->newQuery()->find($id);
    }

    public function all(): Collection
    {
        return $this->model->newQuery()->orderBy('name')->get();
    }

    public function roots(): Collection
    {
        return $this->model->newQuery()->whereNull('parent_id')->with('children')->orderBy('name')->get();
    }

    public function create(array $data): Category
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Category $category, array $data): bool
    {
        return $category->update($data);
    }

    public function delete(Category $category): bool
    {
        return $category->delete();
    }
}
