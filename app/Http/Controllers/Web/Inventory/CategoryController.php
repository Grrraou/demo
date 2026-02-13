<?php

namespace App\Http\Controllers\Web\Inventory;

use App\Http\Controllers\Controller;
use App\Managers\Inventory\CategoryManager;
use App\Models\Inventory\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryManager $categoryManager
    ) {}

    public function index(): View
    {
        $categories = $this->categoryManager->getAll();

        return view('inventory.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $categories = $this->categoryManager->getAll();

        return view('inventory.categories.edit', [
            'category' => new Category,
            'categories' => $categories,
            'isCreate' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:inventory_categories,id'],
        ]);

        $this->categoryManager->create($validated);

        return redirect()->route('inventory.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category): View
    {
        $categories = $this->categoryManager->getAll();

        return view('inventory.categories.edit', [
            'category' => $category,
            'categories' => $categories,
            'isCreate' => false,
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:inventory_categories,id'],
        ]);

        $this->categoryManager->update($category, $validated);

        return redirect()->route('inventory.categories.index')->with('success', 'Category updated.');
    }
}
