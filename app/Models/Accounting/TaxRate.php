<?php

namespace App\Models\Accounting;

use App\Models\OwnedCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TaxRate extends Model
{
    protected $table = 'accounting_tax_rates';

    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED = 'fixed';

    public const TYPES = [
        self::TYPE_PERCENTAGE => 'Percentage',
        self::TYPE_FIXED => 'Fixed Amount',
    ];

    protected $fillable = [
        'owned_company_id',
        'name',
        'code',
        'rate',
        'type',
        'is_compound',
        'is_recoverable',
        'is_active',
        'account_id',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_compound' => 'boolean',
        'is_recoverable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function ownedCompany(): BelongsTo
    {
        return $this->belongsTo(OwnedCompany::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function taxGroups(): BelongsToMany
    {
        return $this->belongsToMany(TaxGroup::class, 'accounting_tax_group_rates')
            ->withPivot('order')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function calculateTax(float $amount): float
    {
        if ($this->type === self::TYPE_FIXED) {
            return (float) $this->rate;
        }

        return $amount * ((float) $this->rate / 100);
    }

    public function getDisplayRateAttribute(): string
    {
        if ($this->type === self::TYPE_FIXED) {
            return number_format($this->rate, 2);
        }

        return number_format($this->rate, 2) . '%';
    }
}
