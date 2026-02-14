<?php

namespace App\Http\Controllers\Web\Accounting;

use App\Http\Controllers\Controller;

class AccountingController extends Controller
{
    public function chartOfAccounts()
    {
        return view('accounting.chart-of-accounts');
    }

    public function taxRates()
    {
        return view('accounting.tax-rates');
    }

    public function journalEntries()
    {
        return view('accounting.journal-entries');
    }

    public function fiscalYears()
    {
        return view('accounting.fiscal-years');
    }

    public function currencies()
    {
        return view('accounting.currencies');
    }

    public function trialBalance()
    {
        return view('accounting.reports.trial-balance');
    }

    public function generalLedger()
    {
        return view('accounting.reports.general-ledger');
    }

    public function taxSummary()
    {
        return view('accounting.reports.tax-summary');
    }

    public function settings()
    {
        return view('accounting.settings');
    }
}
