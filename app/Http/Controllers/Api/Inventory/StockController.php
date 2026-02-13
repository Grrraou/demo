<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Managers\Inventory\StockManager;
use App\Models\Inventory\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function __construct(
        private StockManager $stockManager
    ) {}

    public function index(): JsonResponse
    {
        $stocks = $this->stockManager->getAll();

        return response()->json($stocks);
    }

    public function show(Stock $stock): JsonResponse
    {
        $stock->load(['product', 'location', 'movements' => fn ($q) => $q->orderByDesc('created_at')->limit(20)]);

        return response()->json($stock);
    }

    public function movements(Stock $stock, Request $request): JsonResponse
    {
        $limit = $request->integer('limit', 50);
        $movements = $this->stockManager->getMovements($stock->id, $limit);

        return response()->json($movements);
    }

    public function entry(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'stock_id' => ['required', 'integer', 'exists:inventory_stocks,id'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'reference_id' => ['nullable', 'integer'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $movement = $this->stockManager->recordEntry(
                $validated['stock_id'],
                (float) $validated['quantity'],
                $validated['reference_id'] ?? null,
                $validated['note'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($movement->load('stock.product'), 201);
    }

    public function exit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'stock_id' => ['required', 'integer', 'exists:inventory_stocks,id'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'reference_id' => ['nullable', 'integer'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $movement = $this->stockManager->recordExit(
                $validated['stock_id'],
                (float) $validated['quantity'],
                $validated['reference_id'] ?? null,
                $validated['note'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($movement->load('stock.product'), 201);
    }

    public function transfer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_stock_id' => ['required', 'integer', 'exists:inventory_stocks,id'],
            'to_stock_id' => ['required', 'integer', 'exists:inventory_stocks,id'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['from_stock_id'] === $validated['to_stock_id']) {
            return response()->json(['message' => 'Source and destination must differ.'], 422);
        }

        try {
            $result = $this->stockManager->recordTransfer(
                $validated['from_stock_id'],
                $validated['to_stock_id'],
                (float) $validated['quantity'],
                $validated['note'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result, 201);
    }
}
