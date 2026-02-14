<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\Account;
use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalEntry;
use App\Services\Accounting\JournalService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class JournalEntryForm extends Component
{
    public ?int $entryId = null;
    public bool $isEditing = false;

    // Form fields
    public ?int $journalId = null;
    public string $entryDate = '';
    public string $reference = '';
    public string $description = '';
    public array $lines = [];

    public function mount(?int $entryId = null): void
    {
        $companyId = session('current_owned_company_id');
        if (!$companyId) {
            return;
        }

        // Ensure default journals exist
        JournalService::ensureDefaultJournals($companyId);

        if ($entryId) {
            $this->entryId = $entryId;
            $this->isEditing = true;
            $this->loadEntry();
        } else {
            $this->entryDate = now()->toDateString();
            $this->addLine();
            $this->addLine();

            // Set default journal
            $defaultJournal = Journal::where('owned_company_id', $companyId)
                ->where('type', Journal::TYPE_GENERAL)
                ->first();
            if ($defaultJournal) {
                $this->journalId = $defaultJournal->id;
            }
        }
    }

    public function loadEntry(): void
    {
        $entry = JournalEntry::with('lines')->find($this->entryId);
        if (!$entry || !$entry->canBeEdited()) {
            session()->flash('error', 'Journal entry cannot be edited');
            return;
        }

        $this->journalId = $entry->journal_id;
        $this->entryDate = $entry->entry_date->toDateString();
        $this->reference = $entry->reference ?? '';
        $this->description = $entry->description ?? '';

        $this->lines = [];
        foreach ($entry->lines as $line) {
            $this->lines[] = [
                'account_id' => $line->account_id,
                'description' => $line->description ?? '',
                'debit' => $line->debit > 0 ? (float) $line->debit : null,
                'credit' => $line->credit > 0 ? (float) $line->credit : null,
            ];
        }

        // Ensure at least 2 lines
        while (count($this->lines) < 2) {
            $this->addLine();
        }
    }

    public function addLine(): void
    {
        $this->lines[] = [
            'account_id' => null,
            'description' => '',
            'debit' => null,
            'credit' => null,
        ];
    }

    public function removeLine(int $index): void
    {
        if (count($this->lines) > 2) {
            unset($this->lines[$index]);
            $this->lines = array_values($this->lines);
        }
    }

    public function updatedLines($value, $key): void
    {
        // Auto-balance: if debit is entered, clear credit on same line (and vice versa)
        $parts = explode('.', $key);
        if (count($parts) === 2) {
            $index = (int) $parts[0];
            $field = $parts[1];

            if ($field === 'debit' && $value > 0) {
                $this->lines[$index]['credit'] = null;
            } elseif ($field === 'credit' && $value > 0) {
                $this->lines[$index]['debit'] = null;
            }
        }
    }

    public function save(): void
    {
        if (!Auth::user()->canEditAccounting()) {
            return;
        }

        $this->validate([
            'journalId' => 'required|exists:accounting_journals,id',
            'entryDate' => 'required|date',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounting_accounts,id',
        ]);

        $companyId = session('current_owned_company_id');
        if (!$companyId) return;

        // Prepare lines data
        $preparedLines = [];
        foreach ($this->lines as $line) {
            if (!$line['account_id']) continue;

            $debit = (float) ($line['debit'] ?? 0);
            $credit = (float) ($line['credit'] ?? 0);

            if ($debit == 0 && $credit == 0) continue;

            $preparedLines[] = [
                'account_id' => $line['account_id'],
                'description' => $line['description'] ?? null,
                'debit' => $debit,
                'credit' => $credit,
            ];
        }

        if (count($preparedLines) < 2) {
            session()->flash('error', 'At least two valid lines are required');
            return;
        }

        // Validate balance
        $totalDebit = array_sum(array_column($preparedLines, 'debit'));
        $totalCredit = array_sum(array_column($preparedLines, 'credit'));

        if (abs($totalDebit - $totalCredit) > 0.01) {
            session()->flash('error', 'Journal entry must be balanced. Difference: ' . number_format(abs($totalDebit - $totalCredit), 2));
            return;
        }

        try {
            if ($this->isEditing && $this->entryId) {
                $entry = JournalEntry::find($this->entryId);
                JournalService::updateEntry(
                    $entry,
                    $this->entryDate,
                    $preparedLines,
                    $this->reference ?: null,
                    $this->description ?: null
                );
            } else {
                JournalService::createEntry(
                    $companyId,
                    $this->journalId,
                    $this->entryDate,
                    $preparedLines,
                    $this->reference ?: null,
                    $this->description ?: null,
                    null, // sourceType
                    null, // sourceId
                    null, // currencyCode
                    1.0,  // exchangeRate
                    Auth::id()
                );
            }

            session()->flash('success', 'Journal entry saved successfully');
            $this->redirect(route('accounting.journal-entries'));
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving journal entry: ' . $e->getMessage());
        }
    }

    public function getTotalDebitProperty(): float
    {
        return array_sum(array_map(fn($l) => (float) ($l['debit'] ?? 0), $this->lines));
    }

    public function getTotalCreditProperty(): float
    {
        return array_sum(array_map(fn($l) => (float) ($l['credit'] ?? 0), $this->lines));
    }

    public function getDifferenceProperty(): float
    {
        return abs($this->totalDebit - $this->totalCredit);
    }

    public function getIsBalancedProperty(): bool
    {
        return $this->difference < 0.01;
    }

    public function getJournalsProperty()
    {
        $companyId = session('current_owned_company_id');
        if (!$companyId) return collect();

        return Journal::where('owned_company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getAccountsProperty()
    {
        $companyId = session('current_owned_company_id');
        if (!$companyId) return collect();

        return Account::where('owned_company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type']);
    }

    public function render()
    {
        return view('livewire.accounting.journal-entry-form');
    }
}
