<?php

namespace App\Services\Accounting;

use App\Models\Accounting\Account;
use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalEntry;
use App\Models\OwnedCompany;
use App\Models\Sales\Invoice;
use App\Models\Sales\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoJournalGenerator
{
    /**
     * Generate journal entry for an invoice
     * 
     * Debit: Accounts Receivable (total amount)
     * Credit: Revenue (subtotal)
     * Credit: Tax Payable (tax amount) - if applicable
     */
    public static function generateForInvoice(Invoice $invoice, int $userId): ?JournalEntry
    {
        $companyId = $invoice->salesOrder?->owned_company_id 
            ?? session('current_owned_company_id');
        
        if (!$companyId) {
            Log::warning('AutoJournalGenerator: No company ID for invoice ' . $invoice->id);
            return null;
        }

        // Get or create required accounts
        $receivableAccount = self::findOrCreateAccount($companyId, 'receivable', '1130', 'Accounts Receivable', Account::TYPE_ASSET);
        $revenueAccount = self::findOrCreateAccount($companyId, 'sales', '4100', 'Sales Revenue', Account::TYPE_REVENUE);
        $taxPayableAccount = self::findOrCreateAccount($companyId, 'tax_payable', '2140', 'VAT/GST Payable', Account::TYPE_LIABILITY);

        if (!$receivableAccount || !$revenueAccount) {
            Log::warning('AutoJournalGenerator: Missing accounts for invoice ' . $invoice->id);
            return null;
        }

        // Get or create Sales Journal
        JournalService::ensureDefaultJournals($companyId);
        $journal = Journal::where('owned_company_id', $companyId)
            ->where('type', Journal::TYPE_SALES)
            ->first();

        if (!$journal) {
            Log::warning('AutoJournalGenerator: No sales journal for company ' . $companyId);
            return null;
        }

        // Calculate totals from invoice
        $subtotal = (float) $invoice->subtotal;
        $taxTotal = (float) $invoice->tax_total;
        $total = (float) $invoice->total;

        // If no tax fields, fall back to simple calculation
        if ($total == 0) {
            $total = $invoice->items->sum(fn($item) => $item->quantity * $item->unit_price);
            $subtotal = $total;
            $taxTotal = 0;
        }

        // Build journal lines
        $lines = [
            [
                'account_id' => $receivableAccount->id,
                'description' => 'Invoice ' . $invoice->number,
                'debit' => $total,
                'credit' => 0,
            ],
            [
                'account_id' => $revenueAccount->id,
                'description' => 'Sales revenue - Invoice ' . $invoice->number,
                'debit' => 0,
                'credit' => $subtotal,
            ],
        ];

        // Add tax line if there's tax
        if ($taxTotal > 0 && $taxPayableAccount) {
            $lines[] = [
                'account_id' => $taxPayableAccount->id,
                'description' => 'Tax on Invoice ' . $invoice->number,
                'debit' => 0,
                'credit' => $taxTotal,
            ];
        }

        try {
            $entry = JournalService::createEntry(
                $companyId,
                $journal->id,
                $invoice->invoice_date->toDateString(),
                $lines,
                $invoice->number,
                'Auto-generated from Invoice ' . $invoice->number,
                get_class($invoice),
                $invoice->id,
                $invoice->currency_code ?? 'USD',
                (float) ($invoice->exchange_rate ?? 1),
                $userId
            );

            // Link journal entry to invoice
            if ($entry) {
                $invoice->update(['journal_entry_id' => $entry->id]);
                
                // Auto-post the entry
                $entry->post($userId);
            }

            return $entry;
        } catch (\Exception $e) {
            Log::error('AutoJournalGenerator: Failed to create entry for invoice ' . $invoice->id . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate journal entry for a payment
     * 
     * Debit: Cash/Bank (payment amount)
     * Credit: Accounts Receivable (payment amount)
     */
    public static function generateForPayment(Payment $payment, int $userId): ?JournalEntry
    {
        $invoice = $payment->invoice;
        if (!$invoice) {
            return null;
        }

        $companyId = $invoice->salesOrder?->owned_company_id 
            ?? session('current_owned_company_id');
        
        if (!$companyId) {
            return null;
        }

        // Get accounts
        $bankAccount = self::findOrCreateAccount($companyId, 'bank', '1120', 'Bank Accounts', Account::TYPE_ASSET);
        $receivableAccount = self::findOrCreateAccount($companyId, 'receivable', '1130', 'Accounts Receivable', Account::TYPE_ASSET);

        if (!$bankAccount || !$receivableAccount) {
            return null;
        }

        // Get Cash Receipts Journal
        JournalService::ensureDefaultJournals($companyId);
        $journal = Journal::where('owned_company_id', $companyId)
            ->where('type', Journal::TYPE_CASH)
            ->first();

        if (!$journal) {
            return null;
        }

        $amount = (float) $payment->amount;

        $lines = [
            [
                'account_id' => $bankAccount->id,
                'description' => 'Payment received - ' . ($payment->reference ?? 'Invoice ' . $invoice->number),
                'debit' => $amount,
                'credit' => 0,
            ],
            [
                'account_id' => $receivableAccount->id,
                'description' => 'Payment applied to Invoice ' . $invoice->number,
                'debit' => 0,
                'credit' => $amount,
            ],
        ];

        try {
            $entry = JournalService::createEntry(
                $companyId,
                $journal->id,
                $payment->payment_date->toDateString(),
                $lines,
                $payment->reference ?? $invoice->number,
                'Auto-generated from Payment for Invoice ' . $invoice->number,
                get_class($payment),
                $payment->id,
                $invoice->currency_code ?? 'USD',
                (float) ($invoice->exchange_rate ?? 1),
                $userId
            );

            if ($entry) {
                $entry->post($userId);
            }

            return $entry;
        } catch (\Exception $e) {
            Log::error('AutoJournalGenerator: Failed to create entry for payment ' . $payment->id . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find or create an account by subtype
     */
    protected static function findOrCreateAccount(
        int $companyId,
        string $subtype,
        string $code,
        string $name,
        string $type
    ): ?Account {
        // First try to find by subtype
        $account = Account::where('owned_company_id', $companyId)
            ->where('subtype', $subtype)
            ->where('is_active', true)
            ->first();

        if ($account) {
            return $account;
        }

        // Try to find by code
        $account = Account::where('owned_company_id', $companyId)
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if ($account) {
            return $account;
        }

        // Create the account if it doesn't exist
        return Account::create([
            'owned_company_id' => $companyId,
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'subtype' => $subtype,
            'is_system' => true,
        ]);
    }
}
