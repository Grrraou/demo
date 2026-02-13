<?php

namespace App\Repositories\Sales;

use App\Models\Sales\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class InvoiceRepository
{
    public function __construct(
        private Invoice $model
    ) {}

    public function find(int $id): ?Invoice
    {
        return $this->model->newQuery()->with(['customer', 'salesOrder', 'items.product', 'payments'])->find($id);
    }

    public function all(): Collection
    {
        return $this->model->newQuery()->with('customer')->orderByDesc('invoice_date')->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()->with('customer')->orderByDesc('invoice_date')->paginate($perPage);
    }

    public function create(array $data): Invoice
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Invoice $invoice, array $data): bool
    {
        return $invoice->update($data);
    }

    public function nextNumber(): string
    {
        $last = $this->model->newQuery()->withTrashed()->orderByDesc('id')->first();
        $seq = $last ? ((int) preg_replace('/\D/', '', $last->number)) + 1 : 1;

        return 'INV-' . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }
}
