<?php

namespace App\Http\Controllers\Web\Inventory;

use App\Http\Controllers\Controller;
use App\Managers\Inventory\StockManager;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function __construct(
        private StockManager $stockManager
    ) {}

    public function index(Request $request): View
    {
        $stocks = $this->stockManager->getAll();

        return view('inventory.stocks.index', compact('stocks'));
    }
}
