<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Payment;
use App\Repositories\Sales\PaymentRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentRepository $paymentRepository
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_id' => ['required', 'integer', 'exists:sales_invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['nullable', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $payment = $this->paymentRepository->create([
            'invoice_id' => $validated['invoice_id'],
            'amount' => $validated['amount'],
            'paid_at' => $validated['paid_at'] ?? now(),
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json($payment->load('invoice'), 201);
    }

    public function show(Payment $payment): JsonResponse
    {
        $payment->load('invoice');

        return response()->json($payment);
    }
}
