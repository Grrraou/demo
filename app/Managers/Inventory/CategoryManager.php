<?php

namespace App\Managers\Inventory;

use App\Models\Inventory\Category;
use App\Repositories\Inventory\CategoryRepository;
use Illuminate\Database\Eloquent\Collection;

class CategoryManager
{
    public function __construct(
        private CategoryRepository $categoryRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->categoryRepository->all();
    }

    public function getTree(): Collection
    {
        return $this->categoryRepository->roots();
    }

    public function find(int $id): ?Category
    {
        return $this->categoryRepository->find($id);
    }

    public function create(array $data): Category
    {
        return $this->categoryRepository->create($data);
    }

    public function update(Category $category, array $data): bool
    {
        return $this->categoryRepository->update($category, $data);
    }

    public function delete(Category $category): bool
    {
        return $this->categoryRepository->delete($category);
    }
}
