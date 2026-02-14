<?php

namespace App\Models\Accounting;

use App\Models\OwnedCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journal extends Model
{
    protected $table = 'accounting_journals';

    public const TYPE_GENERAL = 'general';
    public const TYPE_SALES = 'sales';
    public const TYPE_PURCHASES = 'purchases';
    public const TYPE_CASH = 'cash';
    public const TYPE_BANK = 'bank';

    public const TYPES = [
        self::TYPE_GENERAL => 'General Journal',
        self::TYPE_SALES => 'Sales Journal',
        self::TYPE_PURCHASES => 'Purchases Journal',
        self::TYPE_CASH => 'Cash Journal',
        self::TYPE_BANK => 'Bank Journal',
    ];

    protected $fillable = [
        'owned_company_id',
        'code',
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function ownedCompany(): BelongsTo
    {
        return $this->belongsTo(OwnedCompany::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'journal_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
