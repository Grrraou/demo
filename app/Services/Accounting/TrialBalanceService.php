<?php

namespace App\Services\Accounting;

use App\Models\Accounting\Account;
use App\Models\Accounting\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class TrialBalanceService
{
    /**
     * Generate trial balance report
     */
    public static function generate(
        int $companyId,
        ?string $asOfDate = null,
        ?int $fiscalYearId = null
    ): array {
        $asOfDate = $asOfDate ?? now()->toDateString();

        $query = DB::table('accounting_journal_entry_lines as l')
            ->join('accounting_journal_entries as e', 'l.journal_entry_id', '=', 'e.id')
            ->join('accounting_accounts as a', 'l.account_id', '=', 'a.id')
            ->where('e.owned_company_id', $companyId)
            ->where('e.status', 'posted')
            ->where('e.entry_date', '<=', $asOfDate)
            ->select(
                'a.id as account_id',
                'a.code',
                'a.name',
                'a.type',
                'a.subtype',
                DB::raw('SUM(l.debit_base) as total_debit'),
                DB::raw('SUM(l.credit_base) as total_credit')
            )
            ->groupBy('a.id', 'a.code', 'a.name', 'a.type', 'a.subtype');

        if ($fiscalYearId) {
            $query->where('e.fiscal_year_id', $fiscalYearId);
        }

        $results = $query->orderBy('a.code')->get();

        $accounts = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($results as $row) {
            $debit = (float) $row->total_debit;
            $credit = (float) $row->total_credit;

            // Calculate balance based on account type
            $isDebitNormal = in_array($row->type, ['asset', 'expense']);
            
            if ($isDebitNormal) {
                $balance = $debit - $credit;
                $debitBalance = $balance > 0 ? $balance : 0;
                $creditBalance = $balance < 0 ? abs($balance) : 0;
            } else {
                $balance = $credit - $debit;
                $debitBalance = $balance < 0 ? abs($balance) : 0;
                $creditBalance = $balance > 0 ? $balance : 0;
            }

            $totalDebit += $debitBalance;
            $totalCredit += $creditBalance;

            $accounts[] = [
                'account_id' => $row->account_id,
                'code' => $row->code,
                'name' => $row->name,
                'type' => $row->type,
                'subtype' => $row->subtype,
                'debit' => round($debitBalance, 2),
                'credit' => round($creditBalance, 2),
            ];
        }

        return [
            'as_of_date' => $asOfDate,
            'accounts' => $accounts,
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
        ];
    }

    /**
     * Get account balances grouped by type
     */
    public static function getBalancesByType(int $companyId, ?string $asOfDate = null): array
    {
        $trialBalance = self::generate($companyId, $asOfDate);

        $byType = [
            'asset' => ['debit' => 0, 'credit' => 0, 'accounts' => []],
            'liability' => ['debit' => 0, 'credit' => 0, 'accounts' => []],
            'equity' => ['debit' => 0, 'credit' => 0, 'accounts' => []],
            'revenue' => ['debit' => 0, 'credit' => 0, 'accounts' => []],
            'expense' => ['debit' => 0, 'credit' => 0, 'accounts' => []],
        ];

        foreach ($trialBalance['accounts'] as $account) {
            $type = $account['type'];
            if (isset($byType[$type])) {
                $byType[$type]['debit'] += $account['debit'];
                $byType[$type]['credit'] += $account['credit'];
                $byType[$type]['accounts'][] = $account;
            }
        }

        return $byType;
    }
}
