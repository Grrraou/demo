<?php

namespace App\Models\Accounting;

use App\Models\OwnedCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TaxGroup extends Model
{
    protected $table = 'accounting_tax_groups';

    protected $fillable = [
        'owned_company_id',
        'name',
        'description',
    ];

    public function ownedCompany(): BelongsTo
    {
        return $this->belongsTo(OwnedCompany::class);
    }

    public function taxRates(): BelongsToMany
    {
        return $this->belongsToMany(TaxRate::class, 'accounting_tax_group_rates')
            ->withPivot('order')
            ->withTimestamps()
            ->orderByPivot('order');
    }

    public function calculateTax(float $amount): array
    {
        $taxes = [];
        $taxableAmount = $amount;

        foreach ($this->taxRates as $taxRate) {
            $taxAmount = $taxRate->calculateTax($taxableAmount);
            $taxes[] = [
                'tax_rate_id' => $taxRate->id,
                'tax_rate' => $taxRate,
                'amount' => $taxAmount,
            ];

            // If compound, subsequent taxes are calculated on amount + previous taxes
            if ($taxRate->is_compound) {
                $taxableAmount += $taxAmount;
            }
        }

        return $taxes;
    }

    public function getTotalTax(float $amount): float
    {
        $taxes = $this->calculateTax($amount);
        return array_sum(array_column($taxes, 'amount'));
    }

    public function getCombinedRateAttribute(): float
    {
        return $this->taxRates->sum('rate');
    }
}
