<?php

namespace App\Http\Controllers\Web\Inventory;

use App\Http\Controllers\Controller;
use App\Managers\Inventory\CategoryManager;
use App\Managers\Inventory\ProductManager;
use App\Managers\Inventory\UnitManager;
use App\Models\Inventory\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private ProductManager $productManager,
        private CategoryManager $categoryManager,
        private UnitManager $unitManager
    ) {}

    public function index(Request $request): View
    {
        $products = $this->productManager->paginate($request->integer('per_page', 15));

        return view('inventory.products.index', compact('products'));
    }

    public function create(Request $request): View
    {
        $categories = $this->categoryManager->getAll();
        $units = $this->unitManager->getAll();

        return view('inventory.products.edit', [
            'product' => new Product,
            'categories' => $categories,
            'units' => $units,
            'isCreate' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:64', 'unique:inventory_products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit_id' => ['required', 'integer', 'exists:inventory_units,id'],
            'category_id' => ['nullable', 'integer', 'exists:inventory_categories,id'],
        ]);

        $product = $this->productManager->create($validated);

        return redirect()->route('inventory.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        $categories = $this->categoryManager->getAll();
        $units = $this->unitManager->getAll();

        return view('inventory.products.edit', [
            'product' => $product,
            'categories' => $categories,
            'units' => $units,
            'isCreate' => false,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:64', 'unique:inventory_products,sku,' . $product->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit_id' => ['required', 'integer', 'exists:inventory_units,id'],
            'category_id' => ['nullable', 'integer', 'exists:inventory_categories,id'],
        ]);

        $this->productManager->update($product, $validated);

        return redirect()->route('inventory.products.index')->with('success', 'Product updated.');
    }
}
