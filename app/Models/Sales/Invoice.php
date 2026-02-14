<?php

namespace App\Models\Sales;

use App\Enums\Sales\InvoiceStatus;
use App\Models\Accounting\JournalEntry;
use App\Models\CustomerCompany;
use App\Services\Accounting\TaxCalculator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales_invoices';

    protected $fillable = [
        'sales_order_id',
        'customer_company_id',
        'number',
        'status',
        'invoice_date',
        'due_date',
        'notes',
        'currency_code',
        'exchange_rate',
        'subtotal',
        'tax_total',
        'total',
        'journal_entry_id',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'status' => InvoiceStatus::class,
            'exchange_rate' => 'decimal:8',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
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

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function getTotalAttribute(): float
    {
        // Use stored total if available, otherwise calculate
        if ($this->attributes['total'] ?? null) {
            return (float) $this->attributes['total'];
        }
        return (float) $this->items->sum(fn (InvoiceItem $item) => $item->line_total);
    }

    public function getSubtotalAttribute(): float
    {
        if ($this->attributes['subtotal'] ?? null) {
            return (float) $this->attributes['subtotal'];
        }
        return (float) $this->items->sum(fn (InvoiceItem $item) => $item->subtotal);
    }

    public function getTaxTotalAttribute(): float
    {
        if ($this->attributes['tax_total'] ?? null) {
            return (float) $this->attributes['tax_total'];
        }
        return (float) $this->items->sum(fn (InvoiceItem $item) => $item->tax_amount);
    }

    public function getPaidTotalAttribute(): float
    {
        return (float) $this->payments->sum('amount');
    }

    public function getBalanceDueAttribute(): float
    {
        return $this->total - $this->paid_total;
    }

    public function isPaid(): bool
    {
        return $this->balance_due <= 0;
    }

    /**
     * Recalculate and save invoice totals from line items
     */
    public function recalculateTotals(): void
    {
        $lineItems = $this->items->map(fn($item) => [
            'subtotal' => (float) $item->subtotal,
            'tax_amount' => (float) $item->tax_amount,
        ])->toArray();

        $totals = TaxCalculator::calculateDocumentTotals($lineItems);

        $this->update([
            'subtotal' => $totals['subtotal'],
            'tax_total' => $totals['tax_total'],
            'total' => $totals['total'],
        ]);
    }

    /**
     * Get tax breakdown by rate
     */
    public function getTaxBreakdown(): array
    {
        $lineItems = $this->items->map(fn($item) => [
            'tax_rate_id' => $item->tax_rate_id,
            'subtotal' => (float) $item->subtotal,
            'tax_amount' => (float) $item->tax_amount,
        ])->toArray();

        return TaxCalculator::getTaxBreakdown($lineItems);
    }
}
