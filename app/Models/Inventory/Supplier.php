<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'inventory_suppliers';

    protected $fillable = ['name', 'email', 'phone', 'address'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'inventory_product_supplier')
            ->using(ProductSupplier::class)
            ->withPivot('cost_price', 'lead_time_days', 'minimum_order_qty')
            ->withTimestamps();
    }
}
