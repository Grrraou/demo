<?php

namespace App\Models\Sales;

use App\Enums\Sales\OrderStatus;
use App\Models\CustomerCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales_orders';

    protected $fillable = ['quote_id', 'customer_company_id', 'number', 'status', 'order_date', 'notes'];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'status' => OrderStatus::class,
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'quote_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerCompany::class, 'customer_company_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class, 'sales_order_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'sales_order_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'sales_order_id');
    }

    public function getTotalAttribute(): float
    {
        return (float) $this->items->sum(fn (SalesOrderItem $item) => $item->quantity * $item->unit_price);
    }
}
