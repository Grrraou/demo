<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockLocation extends Model
{
    use HasFactory;

    protected $table = 'inventory_stock_locations';

    protected $fillable = ['name', 'type'];

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'location_id');
    }
}
