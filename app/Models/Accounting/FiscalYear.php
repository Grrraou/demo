<?php

namespace App\Models\Accounting;

use App\Models\OwnedCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiscalYear extends Model
{
    protected $table = 'accounting_fiscal_years';

    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_LOCKED = 'locked';

    public const STATUSES = [
        self::STATUS_OPEN => 'Open',
        self::STATUS_CLOSED => 'Closed',
        self::STATUS_LOCKED => 'Locked',
    ];

    protected $fillable = [
        'owned_company_id',
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function ownedCompany(): BelongsTo
    {
        return $this->belongsTo(OwnedCompany::class);
    }

    public function periods(): HasMany
    {
        return $this->hasMany(Period::class, 'fiscal_year_id');
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'fiscal_year_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_LOCKED;
    }

    public function canAcceptEntries(): bool
    {
        return $this->isOpen();
    }

    public function containsDate($date): bool
    {
        $date = Carbon::parse($date);
        return $date->between($this->start_date, $this->end_date);
    }

    public function generatePeriods(): void
    {
        $start = $this->start_date->copy();
        $end = $this->end_date->copy();

        while ($start <= $end) {
            $periodEnd = $start->copy()->endOfMonth();
            if ($periodEnd > $end) {
                $periodEnd = $end;
            }

            $this->periods()->create([
                'name' => $start->format('F Y'),
                'start_date' => $start,
                'end_date' => $periodEnd,
                'status' => Period::STATUS_OPEN,
            ]);

            $start = $periodEnd->copy()->addDay();
        }
    }
}
