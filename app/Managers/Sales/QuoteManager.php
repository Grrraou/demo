<?php

namespace App\Managers\Sales;

use App\Enums\Sales\QuoteStatus;
use App\Models\Sales\Quote;
use App\Models\Sales\QuoteItem;
use App\Repositories\Sales\QuoteItemRepository;
use App\Repositories\Sales\QuoteRepository;

class QuoteManager
{
    public function __construct(
        private QuoteRepository $quoteRepository,
        private QuoteItemRepository $quoteItemRepository
    ) {}

    public function create(int $customerId, array $items, ?string $validUntil = null, ?string $notes = null): Quote
    {
        $quote = $this->quoteRepository->create([
            'customer_company_id' => $customerId,
            'number' => $this->quoteRepository->nextNumber(),
            'status' => QuoteStatus::Draft,
            'valid_until' => $validUntil,
            'notes' => $notes,
        ]);

        foreach ($items as $row) {
            $this->quoteItemRepository->create([
                'quote_id' => $quote->id,
                'product_id' => $row['product_id'],
                'description' => $row['description'] ?? null,
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
            ]);
        }

        return $quote->load('items.product');
    }

    public function send(Quote $quote): Quote
    {
        if ($quote->status !== QuoteStatus::Draft) {
            throw new \InvalidArgumentException('Only draft quotes can be sent.');
        }
        $quote->update(['status' => QuoteStatus::Sent]);

        return $quote->fresh();
    }

    public function accept(Quote $quote): Quote
    {
        if ($quote->status !== QuoteStatus::Sent) {
            throw new \InvalidArgumentException('Only sent quotes can be accepted.');
        }
        $quote->update(['status' => QuoteStatus::Accepted]);

        return $quote->fresh();
    }

    public function reject(Quote $quote): Quote
    {
        if ($quote->status !== QuoteStatus::Sent) {
            throw new \InvalidArgumentException('Only sent quotes can be rejected.');
        }
        $quote->update(['status' => QuoteStatus::Rejected]);

        return $quote->fresh();
    }
}
