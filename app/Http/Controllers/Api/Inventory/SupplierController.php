<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Managers\Inventory\SupplierManager;
use App\Models\Inventory\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(
        private SupplierManager $supplierManager
    ) {}

    public function index(): JsonResponse
    {
        $suppliers = $this->supplierManager->getAll();

        return response()->json($suppliers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
        ]);

        $supplier = $this->supplierManager->create($validated);

        return response()->json($supplier, 201);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        $supplier->load('products');

        return response()->json($supplier);
    }

    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
        ]);

        $this->supplierManager->update($supplier, $validated);

        return response()->json($supplier->fresh());
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        $this->supplierManager->delete($supplier);

        return response()->json(null, 204);
    }
}
