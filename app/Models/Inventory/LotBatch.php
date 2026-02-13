<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotBatch extends Model
{
    use HasFactory;

    protected $table = 'inventory_lot_batches';

    protected $fillable = ['product_id', 'batch_number', 'expiry_date', 'quantity'];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'quantity' => 'decimal:4',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
