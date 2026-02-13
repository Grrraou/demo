<?php

namespace App\Repositories\Sales;

use App\Models\Sales\Quote;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class QuoteRepository
{
    public function __construct(
        private Quote $model
    ) {}

    public function find(int $id): ?Quote
    {
        return $this->model->newQuery()->with(['customer', 'items.product'])->find($id);
    }

    public function all(): Collection
    {
        return $this->model->newQuery()->with('customer')->orderByDesc('created_at')->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()->with('customer')->orderByDesc('created_at')->paginate($perPage);
    }

    public function create(array $data): Quote
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Quote $quote, array $data): bool
    {
        return $quote->update($data);
    }

    public function nextNumber(): string
    {
        $last = $this->model->newQuery()->withTrashed()->orderByDesc('id')->first();
        $seq = $last ? ((int) preg_replace('/\D/', '', $last->number)) + 1 : 1;

        return 'Q-' . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }
}
