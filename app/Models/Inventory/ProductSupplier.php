<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductSupplier extends Pivot
{
    protected $table = 'inventory_product_supplier';

    public $incrementing = true;

    protected $fillable = [
        'product_id',
        'supplier_id',
        'cost_price',
        'lead_time_days',
        'minimum_order_qty',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'minimum_order_qty' => 'decimal:4',
        ];
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
