<?php

namespace App\Repositories\Sales;

use App\Models\Sales\QuoteItem;
use Illuminate\Database\Eloquent\Collection;

class QuoteItemRepository
{
    public function __construct(
        private QuoteItem $model
    ) {}

    public function create(array $data): QuoteItem
    {
        return $this->model->newQuery()->create($data);
    }

    public function getByQuote(int $quoteId): Collection
    {
        return $this->model->newQuery()->where('quote_id', $quoteId)->with('product')->get();
    }

    public function delete(QuoteItem $item): bool
    {
        return $item->delete();
    }
}
