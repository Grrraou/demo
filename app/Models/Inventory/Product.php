<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $table = 'inventory_products';

    protected $fillable = [
        'sku',
        'name',
        'description',
        'unit_id',
        'category_id',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'inventory_product_supplier')
            ->using(ProductSupplier::class)
            ->withPivot('cost_price', 'lead_time_days', 'minimum_order_qty')
            ->withTimestamps();
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'product_id');
    }

    public function lotBatches(): HasMany
    {
        return $this->hasMany(LotBatch::class, 'product_id');
    }
}
