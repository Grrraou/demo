<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Managers\Inventory\CategoryManager;
use App\Models\Inventory\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryManager $categoryManager
    ) {}

    public function index(): JsonResponse
    {
        $categories = $this->categoryManager->getAll();

        return response()->json($categories);
    }

    public function tree(): JsonResponse
    {
        $categories = $this->categoryManager->getTree();

        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:inventory_categories,id'],
        ]);

        $category = $this->categoryManager->create($validated);

        return response()->json($category, 201);
    }

    public function show(Category $category): JsonResponse
    {
        $category->load(['parent', 'children', 'products']);

        return response()->json($category);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:inventory_categories,id'],
        ]);

        $this->categoryManager->update($category, $validated);

        return response()->json($category->fresh());
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->categoryManager->delete($category);

        return response()->json(null, 204);
    }
}
