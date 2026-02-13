<?php

namespace App\Models\Sales;

use App\Enums\Sales\InvoiceStatus;
use App\Models\CustomerCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales_invoices';

    protected $fillable = ['sales_order_id', 'customer_company_id', 'number', 'status', 'invoice_date', 'due_date', 'notes'];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'status' => InvoiceStatus::class,
        ];
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerCompany::class, 'customer_company_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }

    public function getTotalAttribute(): float
    {
        return (float) $this->items->sum(fn (InvoiceItem $item) => $item->quantity * $item->unit_price);
    }

    public function getPaidTotalAttribute(): float
    {
        return (float) $this->payments->sum('amount');
    }
}
