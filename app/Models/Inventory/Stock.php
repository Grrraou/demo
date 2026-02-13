<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stock extends Model
{
    use HasFactory;

    protected $table = 'inventory_stocks';

    protected $fillable = ['product_id', 'location_id', 'quantity', 'reserved'];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'reserved' => 'decimal:4',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class, 'location_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'stock_id');
    }

    /** Available quantity (quantity - reserved). */
    public function getAvailableAttribute(): float
    {
        return (float) $this->quantity - (float) $this->reserved;
    }
}
