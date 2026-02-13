<?php

namespace App\Repositories\Sales;

use App\Models\Sales\SalesOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SalesOrderRepository
{
    public function __construct(
        private SalesOrder $model
    ) {}

    public function find(int $id): ?SalesOrder
    {
        return $this->model->newQuery()->with(['customer', 'quote', 'items.product'])->find($id);
    }

    public function all(): Collection
    {
        return $this->model->newQuery()->with('customer')->orderByDesc('order_date')->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()->with('customer')->orderByDesc('order_date')->paginate($perPage);
    }

    public function create(array $data): SalesOrder
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(SalesOrder $order, array $data): bool
    {
        return $order->update($data);
    }

    public function nextNumber(): string
    {
        $last = $this->model->newQuery()->withTrashed()->orderByDesc('id')->first();
        $seq = $last ? ((int) preg_replace('/\D/', '', $last->number)) + 1 : 1;

        return 'SO-' . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }
}
