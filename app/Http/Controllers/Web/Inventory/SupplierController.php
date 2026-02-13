<?php

namespace App\Http\Controllers\Web\Inventory;

use App\Http\Controllers\Controller;
use App\Managers\Inventory\SupplierManager;
use App\Models\Inventory\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct(
        private SupplierManager $supplierManager
    ) {}

    public function index(): View
    {
        $suppliers = $this->supplierManager->getAll();

        return view('inventory.suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('inventory.suppliers.edit', [
            'supplier' => new Supplier,
            'isCreate' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
        ]);

        $this->supplierManager->create($validated);

        return redirect()->route('inventory.suppliers.index')->with('success', 'Supplier created.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('inventory.suppliers.edit', [
            'supplier' => $supplier,
            'isCreate' => false,
        ]);
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
        ]);

        $this->supplierManager->update($supplier, $validated);

        return redirect()->route('inventory.suppliers.index')->with('success', 'Supplier updated.');
    }
}
