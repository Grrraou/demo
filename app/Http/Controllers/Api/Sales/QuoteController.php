<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Quote;
use App\Managers\Sales\QuoteManager;
use App\Repositories\Sales\QuoteRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function __construct(
        private QuoteRepository $quoteRepository,
        private QuoteManager $quoteManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $quotes = $this->quoteRepository->paginate($perPage);

        return response()->json($quotes);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customer_companies,id'],
            'valid_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:inventory_products,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $quote = $this->quoteManager->create(
                $validated['customer_id'],
                $validated['items'],
                $validated['valid_until'] ?? null,
                $validated['notes'] ?? null
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($quote, 201);
    }

    public function show(Quote $quote): JsonResponse
    {
        $quote->load(['customer', 'items.product']);

        return response()->json($quote);
    }

    public function send(Quote $quote): JsonResponse
    {
        try {
            $quote = $this->quoteManager->send($quote);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($quote);
    }

    public function accept(Quote $quote): JsonResponse
    {
        try {
            $quote = $this->quoteManager->accept($quote);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($quote);
    }

    public function reject(Quote $quote): JsonResponse
    {
        try {
            $quote = $this->quoteManager->reject($quote);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($quote);
    }
}
