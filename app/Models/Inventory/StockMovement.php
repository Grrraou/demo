<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $table = 'inventory_stock_movements';

    public const TYPE_ENTRY = 'entry';
    public const TYPE_EXIT = 'exit';
    public const TYPE_TRANSFER = 'transfer';

    protected $fillable = ['stock_id', 'type', 'quantity', 'reference_id', 'note'];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
        ];
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'stock_id');
    }
}
