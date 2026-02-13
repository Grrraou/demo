<?php

namespace App\Http\Controllers\Web\Inventory;

use App\Http\Controllers\Controller;
use App\Managers\Inventory\StockLocationManager;
use App\Models\Inventory\StockLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockLocationController extends Controller
{
    public function __construct(
        private StockLocationManager $locationManager
    ) {}

    public function index(): View
    {
        $stockLocations = $this->locationManager->getAll();

        return view('inventory.stock-locations.index', compact('stockLocations'));
    }

    public function create(): View
    {
        return view('inventory.stock-locations.edit', [
            'stockLocation' => new StockLocation(['type' => 'warehouse']),
            'isCreate' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:32'],
        ]);

        $this->locationManager->create($validated);

        return redirect()->route('inventory.stock-locations.index')->with('success', 'Location created.');
    }

    public function edit(StockLocation $stockLocation): View
    {
        return view('inventory.stock-locations.edit', [
            'stockLocation' => $stockLocation,
            'isCreate' => false,
        ]);
    }

    public function update(Request $request, StockLocation $stockLocation): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:32'],
        ]);

        $this->locationManager->update($stockLocation, $validated);

        return redirect()->route('inventory.stock-locations.index')->with('success', 'Location updated.');
    }
}
