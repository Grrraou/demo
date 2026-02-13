<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Managers\Inventory\UnitManager;
use App\Models\Inventory\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct(
        private UnitManager $unitManager
    ) {}

    public function index(): JsonResponse
    {
        $units = $this->unitManager->getAll();

        return response()->json($units);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['required', 'string', 'max:32', 'unique:inventory_units,symbol'],
        ]);

        $unit = $this->unitManager->create($validated);

        return response()->json($unit, 201);
    }

    public function show(Unit $unit): JsonResponse
    {
        return response()->json($unit);
    }

    public function update(Request $request, Unit $unit): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'symbol' => ['sometimes', 'string', 'max:32', 'unique:inventory_units,symbol,' . $unit->id],
        ]);

        $this->unitManager->update($unit, $validated);

        return response()->json($unit->fresh());
    }

    public function destroy(Unit $unit): JsonResponse
    {
        $this->unitManager->delete($unit);

        return response()->json(null, 204);
    }
}
