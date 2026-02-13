<?php

namespace App\Models\Sales;

use App\Models\Inventory\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrderItem extends Model
{
    protected $table = 'sales_order_items';

    protected $fillable = ['sales_order_id', 'product_id', 'description', 'quantity', 'unit_price'];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:2',
        ];
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function deliveryItems(): HasMany
    {
        return $this->hasMany(DeliveryItem::class, 'sales_order_item_id');
    }

    public function getLineTotalAttribute(): float
    {
        return (float) $this->quantity * (float) $this->unit_price;
    }

    /** Quantity already delivered across all delivery items. */
    public function getDeliveredQuantityAttribute(): float
    {
        return (float) $this->deliveryItems()->sum('quantity');
    }
}
