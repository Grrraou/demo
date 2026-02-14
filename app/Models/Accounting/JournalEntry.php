<?php

namespace App\Models\Accounting;

use App\Models\OwnedCompany;
use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JournalEntry extends Model
{
    protected $table = 'accounting_journal_entries';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_POSTED = 'posted';
    public const STATUS_REVERSED = 'reversed';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_POSTED => 'Posted',
        self::STATUS_REVERSED => 'Reversed',
    ];

    protected $fillable = [
        'owned_company_id',
        'journal_id',
        'fiscal_year_id',
        'entry_number',
        'entry_date',
        'reference',
        'description',
        'source_type',
        'source_id',
        'currency_code',
        'exchange_rate',
        'status',
        'posted_at',
        'posted_by',
        'reversed_by_id',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'exchange_rate' => 'decimal:8',
        'posted_at' => 'datetime',
    ];

    public function ownedCompany(): BelongsTo
    {
        return $this->belongsTo(OwnedCompany::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class, 'fiscal_year_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'posted_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'created_by');
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'reversed_by_id');
    }

    public function reversalOf(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'reversed_by_id');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopePosted($query)
    {
        return $query->where('status', self::STATUS_POSTED);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPosted(): bool
    {
        return $this->status === self::STATUS_POSTED;
    }

    public function isReversed(): bool
    {
        return $this->status === self::STATUS_REVERSED;
    }

    public function canBeEdited(): bool
    {
        return $this->isDraft();
    }

    public function canBePosted(): bool
    {
        return $this->isDraft() && $this->isBalanced();
    }

    public function canBeReversed(): bool
    {
        return $this->isPosted() && !$this->reversed_by_id;
    }

    public function getTotalDebitAttribute(): float
    {
        return (float) $this->lines->sum('debit');
    }

    public function getTotalCreditAttribute(): float
    {
        return (float) $this->lines->sum('credit');
    }

    public function getTotalDebitBaseAttribute(): float
    {
        return (float) $this->lines->sum('debit_base');
    }

    public function getTotalCreditBaseAttribute(): float
    {
        return (float) $this->lines->sum('credit_base');
    }

    public function isBalanced(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.0001;
    }

    public function post(int $userId): bool
    {
        if (!$this->canBePosted()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_POSTED,
            'posted_at' => now(),
            'posted_by' => $userId,
        ]);

        return true;
    }

    public function reverse(int $userId, ?string $description = null): ?JournalEntry
    {
        if (!$this->canBeReversed()) {
            return null;
        }

        $reversal = static::create([
            'owned_company_id' => $this->owned_company_id,
            'journal_id' => $this->journal_id,
            'fiscal_year_id' => $this->fiscal_year_id,
            'entry_number' => $this->generateEntryNumber(),
            'entry_date' => now()->toDateString(),
            'reference' => 'REV-' . $this->entry_number,
            'description' => $description ?? "Reversal of {$this->entry_number}",
            'currency_code' => $this->currency_code,
            'exchange_rate' => $this->exchange_rate,
            'status' => self::STATUS_DRAFT,
            'created_by' => $userId,
        ]);

        // Create reversed lines (swap debit/credit)
        foreach ($this->lines as $line) {
            $reversal->lines()->create([
                'account_id' => $line->account_id,
                'description' => $line->description,
                'debit' => $line->credit,
                'credit' => $line->debit,
                'debit_base' => $line->credit_base,
                'credit_base' => $line->debit_base,
                'tax_rate_id' => $line->tax_rate_id,
            ]);
        }

        // Post the reversal and mark original as reversed
        $reversal->post($userId);
        
        $this->update([
            'status' => self::STATUS_REVERSED,
            'reversed_by_id' => $reversal->id,
        ]);

        return $reversal;
    }

    public static function generateEntryNumber(): string
    {
        $companyId = session('current_owned_company_id');
        $prefix = 'JE-' . now()->format('Ym') . '-';
        
        $lastEntry = static::where('owned_company_id', $companyId)
            ->where('entry_number', 'like', $prefix . '%')
            ->orderBy('entry_number', 'desc')
            ->first();

        if ($lastEntry) {
            $lastNumber = (int) substr($lastEntry->entry_number, strlen($prefix));
            return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        }

        return $prefix . '0001';
    }
}
