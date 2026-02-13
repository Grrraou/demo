<?php

namespace App\Http\Controllers\Web\Sales;

use App\Http\Controllers\Controller;
use App\Enums\Sales\QuoteStatus;
use App\Models\Inventory\Product;
use App\Models\Sales\Quote;
use App\Managers\Sales\QuoteManager;
use App\Repositories\Sales\QuoteItemRepository;
use App\Repositories\Sales\QuoteRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function __construct(
        private QuoteRepository $quoteRepository,
        private QuoteItemRepository $quoteItemRepository,
        private QuoteManager $quoteManager
    ) {}

    public function index(Request $request): View
    {
        $quotes = $this->quoteRepository->paginate($request->integer('per_page', 15));

        return view('sales.quotes.index', compact('quotes'));
    }

    public function create(): View
    {
        $customers = \App\Models\CustomerCompany::orderBy('name')->get();
        $products = Product::with('unit')->orderBy('name')->get();

        return view('sales.quotes.edit', [
            'quote' => new Quote,
            'customers' => $customers,
            'products' => $products,
            'isCreate' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_company_id' => ['required', 'integer', 'exists:customer_companies,id'],
            'valid_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:inventory_products,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $items = array_map(fn ($row) => [
            'product_id' => (int) $row['product_id'],
            'description' => $row['description'] ?? null,
            'quantity' => (float) $row['quantity'],
            'unit_price' => (float) $row['unit_price'],
        ], $validated['items']);

        $this->quoteManager->create(
            (int) $validated['customer_company_id'],
            $items,
            $validated['valid_until'] ?? null,
            $validated['notes'] ?? null
        );

        return redirect()->route('sales.quotes.index')->with('success', 'Quote created.');
    }

    public function edit(Quote $quote): View
    {
        if ($quote->status !== QuoteStatus::Draft) {
            return view('sales.quotes.show', ['quote' => $quote->load(['customer', 'items.product'])]);
        }

        $customers = \App\Models\CustomerCompany::orderBy('name')->get();
        $products = Product::with('unit')->orderBy('name')->get();
        $quote->load(['customer', 'items.product']);

        return view('sales.quotes.edit', [
            'quote' => $quote,
            'customers' => $customers,
            'products' => $products,
            'isCreate' => false,
        ]);
    }

    public function update(Request $request, Quote $quote): RedirectResponse
    {
        if ($quote->status !== QuoteStatus::Draft) {
            return redirect()->route('sales.quotes.index')->with('error', 'Only draft quotes can be edited.');
        }

        $validated = $request->validate([
            'customer_company_id' => ['required', 'integer', 'exists:customer_companies,id'],
            'valid_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:inventory_products,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $this->quoteRepository->update($quote, [
            'customer_company_id' => $validated['customer_company_id'],
            'valid_until' => $validated['valid_until'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $quote->items()->delete();
        foreach ($validated['items'] as $row) {
            $this->quoteItemRepository->create([
                'quote_id' => $quote->id,
                'product_id' => (int) $row['product_id'],
                'description' => $row['description'] ?? null,
                'quantity' => (float) $row['quantity'],
                'unit_price' => (float) $row['unit_price'],
            ]);
        }

        return redirect()->route('sales.quotes.index')->with('success', 'Quote updated.');
    }

    public function show(Quote $quote): View
    {
        $quote->load(['customer', 'items.product']);

        return view('sales.quotes.show', compact('quote'));
    }
}
