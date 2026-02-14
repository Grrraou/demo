<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CountryConfig extends Model
{
    protected $table = 'accounting_country_configs';

    protected $fillable = [
        'country_code',
        'name',
        'default_currency',
        'accounting_standard',
        'tax_name',
        'config_class',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function taxRates(): HasMany
    {
        return $this->hasMany(CountryTaxRate::class, 'country_code', 'country_code');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getActiveTaxRates($date = null)
    {
        $date = $date ?? now()->toDateString();

        return $this->taxRates()
            ->where('valid_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $date);
            })
            ->get();
    }

    public function getRulesInstance()
    {
        if (!$this->config_class || !class_exists($this->config_class)) {
            return null;
        }

        return new $this->config_class();
    }
}
