<?php

namespace App\Models\Accounting;

use App\Models\OwnedCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $table = 'accounting_accounts';

    public const TYPE_ASSET = 'asset';
    public const TYPE_LIABILITY = 'liability';
    public const TYPE_EQUITY = 'equity';
    public const TYPE_REVENUE = 'revenue';
    public const TYPE_EXPENSE = 'expense';

    public const TYPES = [
        self::TYPE_ASSET => 'Asset',
        self::TYPE_LIABILITY => 'Liability',
        self::TYPE_EQUITY => 'Equity',
        self::TYPE_REVENUE => 'Revenue',
        self::TYPE_EXPENSE => 'Expense',
    ];

    public const SUBTYPES = [
        self::TYPE_ASSET => [
            'current_asset' => 'Current Asset',
            'fixed_asset' => 'Fixed Asset',
            'bank' => 'Bank',
            'cash' => 'Cash',
            'receivable' => 'Accounts Receivable',
            'inventory' => 'Inventory',
            'prepaid' => 'Prepaid Expense',
        ],
        self::TYPE_LIABILITY => [
            'current_liability' => 'Current Liability',
            'long_term_liability' => 'Long-term Liability',
            'payable' => 'Accounts Payable',
            'tax_payable' => 'Tax Payable',
            'accrued' => 'Accrued Liability',
        ],
        self::TYPE_EQUITY => [
            'capital' => 'Capital',
            'retained_earnings' => 'Retained Earnings',
            'drawing' => 'Drawing',
        ],
        self::TYPE_REVENUE => [
            'operating_revenue' => 'Operating Revenue',
            'other_revenue' => 'Other Revenue',
            'sales' => 'Sales',
        ],
        self::TYPE_EXPENSE => [
            'operating_expense' => 'Operating Expense',
            'cost_of_goods' => 'Cost of Goods Sold',
            'payroll' => 'Payroll',
            'depreciation' => 'Depreciation',
            'other_expense' => 'Other Expense',
        ],
    ];

    protected $fillable = [
        'owned_company_id',
        'parent_id',
        'code',
        'name',
        'type',
        'subtype',
        'is_active',
        'is_system',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function ownedCompany(): BelongsTo
    {
        return $this->belongsTo(OwnedCompany::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    public function taxRates(): HasMany
    {
        return $this->hasMany(TaxRate::class, 'account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getFullCodeAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->full_code . '.' . $this->code;
        }
        return $this->code;
    }

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function isDebitNormal(): bool
    {
        return in_array($this->type, [self::TYPE_ASSET, self::TYPE_EXPENSE]);
    }

    public function getBalance(): float
    {
        $lines = $this->journalEntryLines()
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->selectRaw('SUM(debit_base) as total_debit, SUM(credit_base) as total_credit')
            ->first();

        $debit = $lines->total_debit ?? 0;
        $credit = $lines->total_credit ?? 0;

        return $this->isDebitNormal() ? ($debit - $credit) : ($credit - $debit);
    }
}
