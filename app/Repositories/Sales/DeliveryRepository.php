<?php

namespace App\Repositories\Sales;

use App\Models\Sales\Delivery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class DeliveryRepository
{
    public function __construct(
        private Delivery $model
    ) {}

    public function find(int $id): ?Delivery
    {
        return $this->model->newQuery()->with(['salesOrder', 'items.salesOrderItem'])->find($id);
    }

    public function all(): Collection
    {
        return $this->model->newQuery()->with('salesOrder')->orderByDesc('created_at')->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()->with('salesOrder')->orderByDesc('created_at')->paginate($perPage);
    }

    public function create(array $data): Delivery
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Delivery $delivery, array $data): bool
    {
        return $delivery->update($data);
    }

    public function nextNumber(): string
    {
        $last = $this->model->newQuery()->withTrashed()->orderByDesc('id')->first();
        $seq = $last ? ((int) preg_replace('/\D/', '', $last->number)) + 1 : 1;

        return 'DLV-' . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }
}
