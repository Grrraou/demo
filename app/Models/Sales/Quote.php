<?php

namespace App\Models\Sales;

use App\Enums\Sales\QuoteStatus;
use App\Models\CustomerCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'sales_quotes';

    protected $fillable = [
        'customer_company_id', 'number', 'status', 'valid_until', 'notes',
        'currency_code', 'subtotal', 'tax_total', 'total',
    ];

    protected function casts(): array
    {
        return [
            'valid_until' => 'date',
            'status' => QuoteStatus::class,
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerCompany::class, 'customer_company_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class, 'quote_id');
    }

    public function order(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SalesOrder::class, 'quote_id');
    }

    public function getTotalAttribute(): float
    {
        return (float) $this->items->sum(fn (QuoteItem $item) => $item->quantity * $item->unit_price);
    }
}
