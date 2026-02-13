<?php

namespace App\Managers\Inventory;

use App\Models\Inventory\Product;
use App\Models\Inventory\Supplier;
use App\Repositories\Inventory\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductManager
{
    public function __construct(
        private ProductRepository $productRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->productRepository->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->paginate($perPage);
    }

    public function find(int $id): ?Product
    {
        return $this->productRepository->find($id);
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->productRepository->findBySku($sku);
    }

    public function create(array $data): Product
    {
        return $this->productRepository->create($data);
    }

    public function update(Product $product, array $data): bool
    {
        return $this->productRepository->update($product, $data);
    }

    public function delete(Product $product): bool
    {
        return $this->productRepository->delete($product);
    }

    /** @param array<int> $supplierIds */
    public function syncSuppliers(Product $product, array $supplierIds, array $pivotAttributes = []): void
    {
        $sync = [];
        foreach ($supplierIds as $index => $supplierId) {
            $sync[$supplierId] = $pivotAttributes[$index] ?? [];
        }
        $product->suppliers()->sync($sync);
    }

    public function attachSupplier(Product $product, Supplier $supplier, array $pivot = []): void
    {
        $product->suppliers()->attach($supplier->id, $pivot);
    }
}
