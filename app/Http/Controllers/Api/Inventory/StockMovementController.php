<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Managers\Inventory\StockMovementManager;
use App\Models\Inventory\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function __construct(
        private StockMovementManager $movementManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $movements = $this->movementManager->paginate($perPage);

        return response()->json($movements);
    }

    public function show(StockMovement $stockMovement): JsonResponse
    {
        $stockMovement->load('stock.product');

        return response()->json($stockMovement);
    }
}
