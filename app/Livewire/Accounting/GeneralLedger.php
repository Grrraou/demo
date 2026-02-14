<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\Account;
use App\Models\Accounting\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GeneralLedger extends Component
{
    public ?int $accountId = null;
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public array $entries = [];
    public ?array $selectedAccount = null;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function loadLedger(): void
    {
        $companyId = session('current_owned_company_id');
        if (!$companyId || !$this->accountId) {
            $this->entries = [];
            return;
        }

        $account = Account::find($this->accountId);
        if (!$account) {
            return;
        }

        $this->selectedAccount = [
            'id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'type' => $account->type,
            'is_debit_normal' => in_array($account->type, ['asset', 'expense']),
        ];

        // Get opening balance
        $openingBalance = $this->getOpeningBalance($companyId, $this->accountId, $this->dateFrom);

        // Get transactions
        $query = DB::table('accounting_journal_entry_lines as l')
            ->join('accounting_journal_entries as e', 'l.journal_entry_id', '=', 'e.id')
            ->join('accounting_journals as j', 'e.journal_id', '=', 'j.id')
            ->where('l.account_id', $this->accountId)
            ->where('e.owned_company_id', $companyId)
            ->where('e.status', 'posted')
            ->select(
                'e.id as entry_id',
                'e.entry_number',
                'e.entry_date',
                'e.reference',
                'j.name as journal_name',
                'l.description',
                'l.debit_base as debit',
                'l.credit_base as credit'
            )
            ->orderBy('e.entry_date')
            ->orderBy('e.id');

        if ($this->dateFrom) {
            $query->where('e.entry_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->where('e.entry_date', '<=', $this->dateTo);
        }

        $transactions = $query->get();

        $balance = $openingBalance;
        $entries = [];

        // Add opening balance row
        $entries[] = [
            'entry_id' => null,
            'entry_number' => '',
            'entry_date' => $this->dateFrom,
            'reference' => '',
            'journal_name' => '',
            'description' => 'Opening Balance',
            'debit' => null,
            'credit' => null,
            'balance' => $balance,
        ];

        foreach ($transactions as $txn) {
            $debit = (float) $txn->debit;
            $credit = (float) $txn->credit;

            if ($this->selectedAccount['is_debit_normal']) {
                $balance += $debit - $credit;
            } else {
                $balance += $credit - $debit;
            }

            $entries[] = [
                'entry_id' => $txn->entry_id,
                'entry_number' => $txn->entry_number,
                'entry_date' => $txn->entry_date,
                'reference' => $txn->reference,
                'journal_name' => $txn->journal_name,
                'description' => $txn->description,
                'debit' => $debit > 0 ? round($debit, 2) : null,
                'credit' => $credit > 0 ? round($credit, 2) : null,
                'balance' => round($balance, 2),
            ];
        }

        $this->entries = $entries;
    }

    protected function getOpeningBalance(int $companyId, int $accountId, ?string $beforeDate): float
    {
        if (!$beforeDate) {
            return 0;
        }

        $result = DB::table('accounting_journal_entry_lines as l')
            ->join('accounting_journal_entries as e', 'l.journal_entry_id', '=', 'e.id')
            ->where('l.account_id', $accountId)
            ->where('e.owned_company_id', $companyId)
            ->where('e.status', 'posted')
            ->where('e.entry_date', '<', $beforeDate)
            ->select(
                DB::raw('SUM(l.debit_base) as total_debit'),
                DB::raw('SUM(l.credit_base) as total_credit')
            )
            ->first();

        $debit = (float) ($result->total_debit ?? 0);
        $credit = (float) ($result->total_credit ?? 0);

        $account = Account::find($accountId);
        $isDebitNormal = $account && in_array($account->type, ['asset', 'expense']);

        return $isDebitNormal ? ($debit - $credit) : ($credit - $debit);
    }

    public function updatedAccountId(): void
    {
        $this->loadLedger();
    }

    public function updatedDateFrom(): void
    {
        $this->loadLedger();
    }

    public function updatedDateTo(): void
    {
        $this->loadLedger();
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
        return view('livewire.accounting.general-ledger');
    }
}
