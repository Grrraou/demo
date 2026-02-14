<?php

namespace App\Models\Accounting;

use App\Models\OwnedCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRate extends Model
{
    protected $table = 'accounting_exchange_rates';

    protected $fillable = [
        'owned_company_id',
        'from_currency',
        'to_currency',
        'rate',
        'effective_date',
    ];

    protected $casts = [
        'rate' => 'decimal:8',
        'effective_date' => 'date',
    ];

    public function ownedCompany(): BelongsTo
    {
        return $this->belongsTo(OwnedCompany::class);
    }

    public function scopeForPair($query, string $from, string $to)
    {
        return $query->where('from_currency', $from)
            ->where('to_currency', $to);
    }

    public function scopeEffectiveOn($query, $date)
    {
        return $query->where('effective_date', '<=', $date)
            ->orderBy('effective_date', 'desc');
    }

    public static function getRate(int $companyId, string $from, string $to, $date = null): float
    {
        if ($from === $to) {
            return 1.0;
        }

        $date = $date ?? now();

        $rate = static::where('owned_company_id', $companyId)
            ->forPair($from, $to)
            ->effectiveOn($date)
            ->first();

        if ($rate) {
            return (float) $rate->rate;
        }

        // Try reverse rate
        $reverseRate = static::where('owned_company_id', $companyId)
            ->forPair($to, $from)
            ->effectiveOn($date)
            ->first();

        if ($reverseRate && $reverseRate->rate > 0) {
            return 1 / (float) $reverseRate->rate;
        }

        return 1.0; // Default to 1:1 if no rate found
    }

    public static function convert(float $amount, int $companyId, string $from, string $to, $date = null): float
    {
        $rate = static::getRate($companyId, $from, $to, $date);
        return $amount * $rate;
    }
}
