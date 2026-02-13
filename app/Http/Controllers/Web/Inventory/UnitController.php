<?php

namespace App\Http\Controllers\Web\Inventory;

use App\Http\Controllers\Controller;
use App\Managers\Inventory\UnitManager;
use App\Models\Inventory\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function __construct(
        private UnitManager $unitManager
    ) {}

    public function index(): View
    {
        $units = $this->unitManager->getAll();

        return view('inventory.units.index', compact('units'));
    }

    public function create(): View
    {
        return view('inventory.units.edit', [
            'unit' => new Unit,
            'isCreate' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['required', 'string', 'max:32', 'unique:inventory_units,symbol'],
        ]);

        $this->unitManager->create($validated);

        return redirect()->route('inventory.units.index')->with('success', 'Unit created.');
    }

    public function edit(Unit $unit): View
    {
        return view('inventory.units.edit', [
            'unit' => $unit,
            'isCreate' => false,
        ]);
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['required', 'string', 'max:32', 'unique:inventory_units,symbol,' . $unit->id],
        ]);

        $this->unitManager->update($unit, $validated);

        return redirect()->route('inventory.units.index')->with('success', 'Unit updated.');
    }
}
