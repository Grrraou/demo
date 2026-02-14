<?php

namespace App\Models\Sales;

use App\Models\Accounting\TaxRate;
use App\Models\Inventory\Product;
use App\Services\Accounting\TaxCalculator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $table = 'sales_invoice_items';

    protected $fillable = [
        'invoice_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'tax_rate_id',
        'tax_amount',
        'subtotal',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (InvoiceItem $item) {
            $item->calculateTotals();
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }

    public function getLineTotalAttribute(): float
    {
        if ($this->attributes['line_total'] ?? null) {
            return (float) $this->attributes['line_total'];
        }
        return $this->subtotal + $this->tax_amount;
    }

    public function getSubtotalAttribute(): float
    {
        if ($this->attributes['subtotal'] ?? null) {
            return (float) $this->attributes['subtotal'];
        }
        return (float) $this->quantity * (float) $this->unit_price;
    }

    public function getTaxAmountAttribute(): float
    {
        if ($this->attributes['tax_amount'] ?? null) {
            return (float) $this->attributes['tax_amount'];
        }
        return 0;
    }

    /**
     * Calculate and set subtotal, tax_amount, and line_total
     */
    public function calculateTotals(): void
    {
        $result = TaxCalculator::calculateLineItem(
            (float) $this->quantity,
            (float) $this->unit_price,
            $this->tax_rate_id
        );

        $this->attributes['subtotal'] = $result['subtotal'];
        $this->attributes['tax_amount'] = $result['tax_amount'];
        $this->attributes['line_total'] = $result['total'];
    }
}
