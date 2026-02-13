<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    public function __construct(
        private Product $model
    ) {}

    public function find(int $id): ?Product
    {
        return $this->model->newQuery()->find($id);
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->model->newQuery()->where('sku', $sku)->first();
    }

    public function all(): Collection
    {
        return $this->model->newQuery()->with('category')->orderBy('sku')->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()->with(['category', 'unit'])->orderBy('sku')->paginate($perPage);
    }

    public function create(array $data): Product
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Product $product, array $data): bool
    {
        return $product->update($data);
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }
}
