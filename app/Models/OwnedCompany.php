<?php

namespace App\Models;

use App\Models\Accounting\Account;
use App\Models\Accounting\ExchangeRate;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\TaxGroup;
use App\Models\Accounting\TaxRate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OwnedCompany extends Model
{
    /** Logos live under public/company-logos/ so they can be committed. */
    public const LOGO_DIR = 'company-logos';

    public const ACCOUNTING_STANDARDS = [
        'IFRS' => 'International Financial Reporting Standards',
        'GAAP' => 'Generally Accepted Accounting Principles (US)',
        'PCG' => 'Plan Comptable Général (France)',
        'HGB' => 'Handelsgesetzbuch (Germany)',
        'UK_GAAP' => 'UK Generally Accepted Accounting Practice',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'logo',
        'country_code',
        'currency_code',
        'fiscal_year_start_month',
        'accounting_standard',
        'tax_enabled',
    ];

    protected $casts = [
        'fiscal_year_start_month' => 'integer',
        'tax_enabled' => 'boolean',
    ];

    protected $attributes = [
        'currency_code' => 'USD',
        'fiscal_year_start_month' => 1,
        'tax_enabled' => true,
    ];

    public function logoUrl(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        return asset(self::LOGO_DIR . '/' . basename($this->logo));
    }

    public function teamMembers(): BelongsToMany
    {
        return $this->belongsToMany(TeamMember::class, 'owned_company_team_member');
    }

    public function customerCompanies(): BelongsToMany
    {
        return $this->belongsToMany(CustomerCompany::class, 'customer_company_owned_company');
    }

    // Accounting relationships
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function fiscalYears(): HasMany
    {
        return $this->hasMany(FiscalYear::class);
    }

    public function journals(): HasMany
    {
        return $this->hasMany(Journal::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function taxRates(): HasMany
    {
        return $this->hasMany(TaxRate::class);
    }

    public function taxGroups(): HasMany
    {
        return $this->hasMany(TaxGroup::class);
    }

    public function exchangeRates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class);
    }

    // Accounting helpers
    public function getCurrentFiscalYear()
    {
        return $this->fiscalYears()
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();
    }

    public function getOpenFiscalYear()
    {
        return $this->fiscalYears()
            ->where('status', FiscalYear::STATUS_OPEN)
            ->orderBy('start_date', 'desc')
            ->first();
    }

    public function getDefaultJournal(string $type = Journal::TYPE_GENERAL)
    {
        return $this->journals()
            ->where('type', $type)
            ->where('is_active', true)
            ->first();
    }

    public function getAccountByCode(string $code)
    {
        return $this->accounts()
            ->where('code', $code)
            ->first();
    }

    public function getAccountBySubtype(string $subtype)
    {
        return $this->accounts()
            ->where('subtype', $subtype)
            ->where('is_active', true)
            ->first();
    }
}
