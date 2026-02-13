<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Managers\Inventory\LotBatchManager;
use App\Models\Inventory\LotBatch;
use App\Models\Inventory\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LotBatchController extends Controller
{
    public function __construct(
        private LotBatchManager $lotBatchManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $productId = $request->integer('product_id');
        if (! $productId) {
            return response()->json(['message' => 'product_id is required'], 422);
        }
        $batches = $this->lotBatchManager->getByProduct($productId);

        return response()->json($batches);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:inventory_products,id'],
            'batch_number' => ['required', 'string', 'max:64'],
            'expiry_date' => ['nullable', 'date'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
        ]);

        $lotBatch = $this->lotBatchManager->create($validated);

        return response()->json($lotBatch->load('product'), 201);
    }

    public function show(LotBatch $lotBatch): JsonResponse
    {
        $lotBatch->load('product');

        return response()->json($lotBatch);
    }

    public function update(Request $request, LotBatch $lotBatch): JsonResponse
    {
        $validated = $request->validate([
            'batch_number' => ['sometimes', 'string', 'max:64'],
            'expiry_date' => ['nullable', 'date'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->lotBatchManager->update($lotBatch, $validated);

        return response()->json($lotBatch->fresh()->load('product'));
    }

    public function destroy(LotBatch $lotBatch): JsonResponse
    {
        $this->lotBatchManager->delete($lotBatch);

        return response()->json(null, 204);
    }
}
