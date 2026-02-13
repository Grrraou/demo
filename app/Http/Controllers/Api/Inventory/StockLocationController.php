<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Managers\Inventory\StockLocationManager;
use App\Models\Inventory\StockLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockLocationController extends Controller
{
    public function __construct(
        private StockLocationManager $locationManager
    ) {}

    public function index(): JsonResponse
    {
        $locations = $this->locationManager->getAll();

        return response()->json($locations);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:32'],
        ]);

        $location = $this->locationManager->create($validated);

        return response()->json($location, 201);
    }

    public function show(StockLocation $stockLocation): JsonResponse
    {
        $stockLocation->load('stocks.product');

        return response()->json($stockLocation);
    }

    public function update(Request $request, StockLocation $stockLocation): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:32'],
        ]);

        $this->locationManager->update($stockLocation, $validated);

        return response()->json($stockLocation->fresh());
    }

    public function destroy(StockLocation $stockLocation): JsonResponse
    {
        $this->locationManager->delete($stockLocation);

        return response()->json(null, 204);
    }
}
