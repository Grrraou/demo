<?php

namespace App\Models\Sales;

use App\Enums\Sales\DeliveryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales_deliveries';

    protected $fillable = ['sales_order_id', 'number', 'status', 'delivered_at', 'notes'];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
            'status' => DeliveryStatus::class,
        ];
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryItem::class, 'delivery_id');
    }
}
