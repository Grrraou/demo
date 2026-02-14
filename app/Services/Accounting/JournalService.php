<?php

namespace App\Services\Accounting;

use App\Models\Accounting\Account;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalEntryLine;
use App\Models\OwnedCompany;
use Illuminate\Support\Facades\DB;

class JournalService
{
    /**
     * Create a new journal entry
     */
    public static function createEntry(
        int $companyId,
        int $journalId,
        string $entryDate,
        array $lines,
        ?string $reference = null,
        ?string $description = null,
        ?string $sourceType = null,
        ?int $sourceId = null,
        ?string $currencyCode = null,
        float $exchangeRate = 1.0,
        ?int $createdBy = null
    ): ?JournalEntry {
        // Validate company and journal
        $company = OwnedCompany::find($companyId);
        if (!$company) {
            throw new \Exception('Company not found');
        }

        $journal = Journal::where('owned_company_id', $companyId)
            ->where('id', $journalId)
            ->first();
        if (!$journal) {
            throw new \Exception('Journal not found');
        }

        // Find fiscal year for the entry date
        $fiscalYear = FiscalYear::where('owned_company_id', $companyId)
            ->forDate($entryDate)
            ->first();

        if (!$fiscalYear) {
            throw new \Exception('No fiscal year found for the entry date');
        }

        if (!$fiscalYear->canAcceptEntries()) {
            throw new \Exception('Fiscal year is closed');
        }

        // Validate lines balance
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($lines as $line) {
            $totalDebit += $line['debit'] ?? 0;
            $totalCredit += $line['credit'] ?? 0;
        }

        if (abs($totalDebit - $totalCredit) > 0.0001) {
            throw new \Exception('Journal entry is not balanced. Debit: ' . $totalDebit . ', Credit: ' . $totalCredit);
        }

        // Generate entry number
        $entryNumber = static::generateEntryNumber($companyId);

        return DB::transaction(function () use (
            $companyId, $journalId, $fiscalYear, $entryNumber, $entryDate, $lines,
            $reference, $description, $sourceType, $sourceId, $currencyCode, $exchangeRate, $createdBy, $company
        ) {
            $currencyCode = $currencyCode ?? $company->currency_code ?? 'USD';

            $entry = JournalEntry::create([
                'owned_company_id' => $companyId,
                'journal_id' => $journalId,
                'fiscal_year_id' => $fiscalYear->id,
                'entry_number' => $entryNumber,
                'entry_date' => $entryDate,
                'reference' => $reference,
                'description' => $description,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'currency_code' => $currencyCode,
                'exchange_rate' => $exchangeRate,
                'status' => JournalEntry::STATUS_DRAFT,
                'created_by' => $createdBy,
            ]);

            foreach ($lines as $line) {
                $debit = $line['debit'] ?? 0;
                $credit = $line['credit'] ?? 0;

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit' => $debit,
                    'credit' => $credit,
                    'debit_base' => $debit * $exchangeRate,
                    'credit_base' => $credit * $exchangeRate,
                    'tax_rate_id' => $line['tax_rate_id'] ?? null,
                ]);
            }

            return $entry->fresh(['lines', 'journal', 'fiscalYear']);
        });
    }

    /**
     * Update a journal entry (only if draft)
     */
    public static function updateEntry(
        JournalEntry $entry,
        string $entryDate,
        array $lines,
        ?string $reference = null,
        ?string $description = null
    ): JournalEntry {
        if (!$entry->canBeEdited()) {
            throw new \Exception('Journal entry cannot be edited');
        }

        // Validate lines balance
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($lines as $line) {
            $totalDebit += $line['debit'] ?? 0;
            $totalCredit += $line['credit'] ?? 0;
        }

        if (abs($totalDebit - $totalCredit) > 0.0001) {
            throw new \Exception('Journal entry is not balanced');
        }

        return DB::transaction(function () use ($entry, $entryDate, $lines, $reference, $description) {
            $entry->update([
                'entry_date' => $entryDate,
                'reference' => $reference,
                'description' => $description,
            ]);

            // Delete existing lines and recreate
            $entry->lines()->delete();

            foreach ($lines as $line) {
                $debit = $line['debit'] ?? 0;
                $credit = $line['credit'] ?? 0;

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit' => $debit,
                    'credit' => $credit,
                    'debit_base' => $debit * $entry->exchange_rate,
                    'credit_base' => $credit * $entry->exchange_rate,
                    'tax_rate_id' => $line['tax_rate_id'] ?? null,
                ]);
            }

            return $entry->fresh(['lines', 'journal', 'fiscalYear']);
        });
    }

    /**
     * Post a journal entry
     */
    public static function postEntry(JournalEntry $entry, int $userId): bool
    {
        return $entry->post($userId);
    }

    /**
     * Reverse a posted journal entry
     */
    public static function reverseEntry(JournalEntry $entry, int $userId, ?string $description = null): ?JournalEntry
    {
        return $entry->reverse($userId, $description);
    }

    /**
     * Delete a draft journal entry
     */
    public static function deleteEntry(JournalEntry $entry): bool
    {
        if (!$entry->canBeEdited()) {
            throw new \Exception('Cannot delete a posted journal entry');
        }

        return DB::transaction(function () use ($entry) {
            $entry->lines()->delete();
            return $entry->delete();
        });
    }

    /**
     * Generate a unique entry number
     */
    public static function generateEntryNumber(int $companyId): string
    {
        $prefix = 'JE-' . now()->format('Ym') . '-';

        $lastEntry = JournalEntry::where('owned_company_id', $companyId)
            ->where('entry_number', 'like', $prefix . '%')
            ->orderBy('entry_number', 'desc')
            ->first();

        if ($lastEntry) {
            $lastNumber = (int) substr($lastEntry->entry_number, strlen($prefix));
            return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        }

        return $prefix . '0001';
    }

    /**
     * Get or create default journals for a company
     */
    public static function ensureDefaultJournals(int $companyId): void
    {
        $defaultJournals = [
            ['code' => 'GJ', 'name' => 'General Journal', 'type' => Journal::TYPE_GENERAL],
            ['code' => 'SJ', 'name' => 'Sales Journal', 'type' => Journal::TYPE_SALES],
            ['code' => 'PJ', 'name' => 'Purchases Journal', 'type' => Journal::TYPE_PURCHASES],
            ['code' => 'CRJ', 'name' => 'Cash Receipts Journal', 'type' => Journal::TYPE_CASH],
            ['code' => 'CPJ', 'name' => 'Cash Payments Journal', 'type' => Journal::TYPE_CASH],
            ['code' => 'BJ', 'name' => 'Bank Journal', 'type' => Journal::TYPE_BANK],
        ];

        foreach ($defaultJournals as $journal) {
            Journal::firstOrCreate(
                ['owned_company_id' => $companyId, 'code' => $journal['code']],
                array_merge($journal, ['owned_company_id' => $companyId])
            );
        }
    }
}
