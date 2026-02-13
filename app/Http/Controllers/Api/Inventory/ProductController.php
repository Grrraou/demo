<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Managers\Inventory\ProductManager;
use App\Models\Inventory\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private ProductManager $productManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $products = $this->productManager->paginate($perPage);

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:64', 'unique:inventory_products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit_id' => ['required', 'integer', 'exists:inventory_units,id'],
            'category_id' => ['nullable', 'integer', 'exists:inventory_categories,id'],
        ]);

        $product = $this->productManager->create($validated);

        return response()->json($product->load(['category', 'unit']), 201);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'unit', 'suppliers', 'stocks.location', 'lotBatches']);

        return response()->json($product);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'sku' => ['sometimes', 'string', 'max:64', 'unique:inventory_products,sku,' . $product->id],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit_id' => ['sometimes', 'integer', 'exists:inventory_units,id'],
            'category_id' => ['nullable', 'integer', 'exists:inventory_categories,id'],
        ]);

        $this->productManager->update($product, $validated);

        return response()->json($product->fresh()->load(['category', 'unit']));
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->productManager->delete($product);

        return response()->json(null, 204);
    }
}
