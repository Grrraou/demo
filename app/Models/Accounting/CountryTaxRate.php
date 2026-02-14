<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryTaxRate extends Model
{
    protected $table = 'accounting_country_tax_rates';

    public const CATEGORY_STANDARD = 'standard';
    public const CATEGORY_REDUCED = 'reduced';
    public const CATEGORY_ZERO = 'zero';
    public const CATEGORY_EXEMPT = 'exempt';

    public const CATEGORIES = [
        self::CATEGORY_STANDARD => 'Standard Rate',
        self::CATEGORY_REDUCED => 'Reduced Rate',
        self::CATEGORY_ZERO => 'Zero Rate',
        self::CATEGORY_EXEMPT => 'Exempt',
    ];

    protected $fillable = [
        'country_code',
        'name',
        'code',
        'rate',
        'category',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    public function countryConfig(): BelongsTo
    {
        return $this->belongsTo(CountryConfig::class, 'country_code', 'country_code');
    }

    public function scopeActive($query, $date = null)
    {
        $date = $date ?? now()->toDateString();

        return $query->where('valid_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $date);
            });
    }

    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function isActive($date = null): bool
    {
        $date = $date ?? now();

        if ($this->valid_from > $date) {
            return false;
        }

        if ($this->valid_until && $this->valid_until < $date) {
            return false;
        }

        return true;
    }

    public function getCategoryNameAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
