<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'inventory_units';

    protected $fillable = ['name', 'symbol'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'unit_id');
    }
}
