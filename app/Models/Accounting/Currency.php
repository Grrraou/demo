<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = 'accounting_currencies';

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'decimal_places',
        'is_active',
    ];

    protected $casts = [
        'decimal_places' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function format(float $amount): string
    {
        return $this->symbol . number_format($amount, $this->decimal_places);
    }
}
