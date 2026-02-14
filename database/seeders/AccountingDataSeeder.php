<?php

namespace Database\Seeders;

use App\Enums\Sales\InvoiceStatus;
use App\Enums\Sales\OrderStatus;
use App\Enums\Sales\QuoteStatus;
use App\Models\Accounting\Account;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalEntryLine;
use App\Models\Accounting\TaxRate;
use App\Models\CustomerCompany;
use App\Models\Inventory\Product;
use App\Models\OwnedCompany;
use App\Models\Sales\Invoice;
use App\Models\Sales\InvoiceItem;
use App\Models\Sales\Payment;
use App\Models\Sales\Quote;
use App\Models\Sales\QuoteItem;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\TeamMember;
use App\Services\Accounting\JournalService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountingDataSeeder extends Seeder
{
    protected array $customerNames = [
        'Acme Corporation' => ['email' => 'orders@acme-corp.test', 'phone' => '+1-555-1001', 'address' => '123 Industrial Blvd, Chicago, IL'],
        'TechStart Solutions' => ['email' => 'procurement@techstart.test', 'phone' => '+1-555-1002', 'address' => '456 Innovation Dr, San Jose, CA'],
        'Global Trade Partners' => ['email' => 'buying@globaltp.test', 'phone' => '+1-555-1003', 'address' => '789 Commerce Way, New York, NY'],
        'Sunrise Industries' => ['email' => 'orders@sunrise-ind.test', 'phone' => '+1-555-1004', 'address' => '321 Morning Lane, Phoenix, AZ'],
        'Metro Supplies Ltd' => ['email' => 'purchasing@metrosupplies.test', 'phone' => '+1-555-1005', 'address' => '654 Main Street, Seattle, WA'],
        'Premier Distributors' => ['email' => 'orders@premier-dist.test', 'phone' => '+1-555-1006', 'address' => '987 Distribution Ave, Denver, CO'],
        'Atlas Manufacturing' => ['email' => 'supply@atlas-mfg.test', 'phone' => '+1-555-1007', 'address' => '147 Factory Rd, Detroit, MI'],
        'Coastal Enterprises' => ['email' => 'procurement@coastal-ent.test', 'phone' => '+1-555-1008', 'address' => '258 Ocean Blvd, Miami, FL'],
    ];

    public function run(): void
    {
        $companies = OwnedCompany::all();
        if ($companies->isEmpty()) {
            $this->command->warn('No owned companies found. Skipping accounting data seeder.');
            return;
        }

        $admin = TeamMember::whereHas('roles', fn($q) => $q->where('slug', 'admin'))->first();
        $userId = $admin?->id ?? 1;

        foreach ($companies as $company) {
            $this->command->info("Seeding accounting data for: {$company->name}");
            
            // 1. Create Fiscal Years
            $this->createFiscalYears($company);
            
            // 2. Create Chart of Accounts
            $this->createChartOfAccounts($company);
            
            // 3. Create Journals
            JournalService::ensureDefaultJournals($company->id);
            
            // 4. Create Tax Rates
            $this->createTaxRates($company);
            
            // 5. Create Customers
            $customers = $this->createCustomers($company);
            
            // 6. Create Sales Data with Accounting Entries
            $this->createSalesWithAccounting($company, $customers, $userId);
        }
    }

    protected function createFiscalYears(OwnedCompany $company): void
    {
        // Current year
        $currentYear = now()->year;
        
        // Create fiscal years for last year, current year, and next year
        for ($year = $currentYear - 1; $year <= $currentYear; $year++) {
            $startMonth = $company->fiscal_year_start_month ?? 1;
            $startDate = Carbon::create($year, $startMonth, 1);
            $endDate = $startDate->copy()->addYear()->subDay();

            $status = $year < $currentYear ? FiscalYear::STATUS_CLOSED : FiscalYear::STATUS_OPEN;

            $fiscalYear = FiscalYear::firstOrCreate(
                [
                    'owned_company_id' => $company->id,
                    'start_date' => $startDate,
                ],
                [
                    'name' => "FY {$year}",
                    'end_date' => $endDate,
                    'status' => $status,
                ]
            );

            // Generate periods if not exist
            if ($fiscalYear->periods()->count() === 0) {
                $fiscalYear->generatePeriods();
            }
        }
    }

    protected function createChartOfAccounts(OwnedCompany $company): void
    {
        // Skip if accounts already exist
        if ($company->accounts()->count() > 0) {
            return;
        }

        AccountingChartOfAccountsSeeder::createForCompany($company->id);
    }

    protected function createTaxRates(OwnedCompany $company): void
    {
        // Skip if tax rates already exist
        if ($company->taxRates()->count() > 0) {
            return;
        }

        $taxAccount = $company->accounts()
            ->where('subtype', 'tax_payable')
            ->first();

        $taxRates = [
            ['name' => 'No Tax', 'code' => 'NOTAX', 'rate' => 0, 'type' => 'percentage'],
            ['name' => 'Standard Tax (10%)', 'code' => 'TAX10', 'rate' => 10, 'type' => 'percentage'],
            ['name' => 'Reduced Tax (5%)', 'code' => 'TAX5', 'rate' => 5, 'type' => 'percentage'],
            ['name' => 'Premium Tax (20%)', 'code' => 'TAX20', 'rate' => 20, 'type' => 'percentage'],
        ];

        foreach ($taxRates as $taxRate) {
            TaxRate::firstOrCreate(
                [
                    'owned_company_id' => $company->id,
                    'code' => $taxRate['code'],
                ],
                array_merge($taxRate, [
                    'owned_company_id' => $company->id,
                    'account_id' => $taxAccount?->id,
                    'is_active' => true,
                ])
            );
        }
    }

    protected function createCustomers(OwnedCompany $company): array
    {
        $customers = [];

        foreach ($this->customerNames as $name => $data) {
            $customer = CustomerCompany::firstOrCreate(
                ['email' => $data['email']],
                array_merge(['name' => $name], $data)
            );

            // Attach to owned company if not already
            if (!$customer->ownedCompanies()->where('owned_company_id', $company->id)->exists()) {
                $customer->ownedCompanies()->attach($company->id);
            }

            $customers[] = $customer;
        }

        return $customers;
    }

    protected function createSalesWithAccounting(OwnedCompany $company, array $customers, int $userId): void
    {
        $products = Product::orderBy('id')->limit(10)->get();
        if ($products->isEmpty()) {
            $this->command->warn('No products found. Skipping sales creation.');
            return;
        }

        $taxRates = $company->taxRates()->where('is_active', true)->get();
        $fiscalYears = $company->fiscalYears()->get();
        
        if ($fiscalYears->isEmpty()) {
            $this->command->warn('No fiscal years found. Skipping sales creation.');
            return;
        }

        $salesJournal = $company->journals()->where('type', Journal::TYPE_SALES)->first();
        $cashJournal = $company->journals()->where('type', Journal::TYPE_CASH)->first();
        
        $receivableAccount = $company->accounts()->where('subtype', 'receivable')->first();
        $revenueAccount = $company->accounts()->where('subtype', 'sales')->first();
        $taxAccount = $company->accounts()->where('subtype', 'tax_payable')->first();
        $bankAccount = $company->accounts()->where('subtype', 'bank')->first();

        if (!$receivableAccount || !$revenueAccount || !$bankAccount) {
            $this->command->warn('Missing required accounts. Skipping sales creation.');
            return;
        }

        $quoteNumber = 1;
        $orderNumber = 1;
        $invoiceNumber = 1;
        $entryNumber = 1;

        // Create sales for the past 6 months
        for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
            $baseDate = now()->subMonths($monthsAgo);
            
            // Create 3-5 sales per month
            $salesCount = rand(3, 5);
            
            for ($s = 0; $s < $salesCount; $s++) {
                $customer = $customers[array_rand($customers)];
                $selectedProducts = $products->random(rand(1, min(4, $products->count())));
                $taxRate = $taxRates->isNotEmpty() ? $taxRates->random() : null;
                
                // Random date within the month
                $saleDate = $baseDate->copy()->startOfMonth()->addDays(rand(0, 27));
                
                // Determine if this will become a paid invoice
                $willBePaid = rand(1, 10) <= 8; // 80% paid
                $willBePartialPaid = !$willBePaid && rand(1, 10) <= 5; // 50% of unpaid are partial

                // Create Quote
                $quote = Quote::create([
                    'customer_company_id' => $customer->id,
                    'number' => sprintf('Q-%s-%04d', $company->id, $quoteNumber++),
                    'status' => QuoteStatus::Accepted,
                    'valid_until' => $saleDate->copy()->addDays(30),
                    'notes' => 'Generated by seeder',
                    'currency_code' => $company->currency_code ?? 'USD',
                ]);

                $quoteSubtotal = 0;
                $quoteTaxTotal = 0;

                foreach ($selectedProducts as $product) {
                    $qty = rand(1, 20);
                    $price = rand(50, 500) + (rand(0, 99) / 100);
                    $subtotal = $qty * $price;
                    $taxAmount = $taxRate ? $subtotal * ($taxRate->rate / 100) : 0;

                    QuoteItem::create([
                        'quote_id' => $quote->id,
                        'product_id' => $product->id,
                        'description' => $product->name,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'tax_rate_id' => $taxRate?->id,
                        'tax_amount' => round($taxAmount, 2),
                        'subtotal' => round($subtotal, 2),
                        'line_total' => round($subtotal + $taxAmount, 2),
                    ]);

                    $quoteSubtotal += $subtotal;
                    $quoteTaxTotal += $taxAmount;
                }

                $quote->update([
                    'subtotal' => round($quoteSubtotal, 2),
                    'tax_total' => round($quoteTaxTotal, 2),
                    'total' => round($quoteSubtotal + $quoteTaxTotal, 2),
                ]);

                // Create Sales Order
                $order = SalesOrder::create([
                    'quote_id' => $quote->id,
                    'customer_company_id' => $customer->id,
                    'number' => sprintf('SO-%s-%04d', $company->id, $orderNumber++),
                    'status' => OrderStatus::Confirmed,
                    'order_date' => $saleDate,
                    'notes' => null,
                    'currency_code' => $company->currency_code ?? 'USD',
                ]);

                $orderSubtotal = 0;
                $orderTaxTotal = 0;

                foreach ($quote->items as $item) {
                    SalesOrderItem::create([
                        'sales_order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'tax_rate_id' => $item->tax_rate_id,
                        'tax_amount' => $item->tax_amount,
                        'subtotal' => $item->subtotal,
                        'line_total' => $item->line_total,
                    ]);

                    $orderSubtotal += $item->subtotal;
                    $orderTaxTotal += $item->tax_amount;
                }

                $order->update([
                    'subtotal' => round($orderSubtotal, 2),
                    'tax_total' => round($orderTaxTotal, 2),
                    'total' => round($orderSubtotal + $orderTaxTotal, 2),
                ]);

                // Create Invoice
                $invoiceDate = $saleDate->copy()->addDays(rand(1, 3));
                $dueDate = $invoiceDate->copy()->addDays(30);
                
                $invoiceStatus = $willBePaid ? InvoiceStatus::Paid : 
                    ($willBePartialPaid ? InvoiceStatus::Sent : 
                    ($dueDate->isPast() ? InvoiceStatus::Overdue : InvoiceStatus::Sent));

                $invoice = Invoice::create([
                    'sales_order_id' => $order->id,
                    'customer_company_id' => $customer->id,
                    'number' => sprintf('INV-%s-%04d', $company->id, $invoiceNumber++),
                    'status' => $invoiceStatus,
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'notes' => null,
                    'currency_code' => $company->currency_code ?? 'USD',
                    'exchange_rate' => 1,
                ]);

                $invoiceSubtotal = 0;
                $invoiceTaxTotal = 0;

                foreach ($order->items as $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $item->product_id,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'tax_rate_id' => $item->tax_rate_id,
                        'tax_amount' => $item->tax_amount,
                        'subtotal' => $item->subtotal,
                        'line_total' => $item->line_total,
                    ]);

                    $invoiceSubtotal += $item->subtotal;
                    $invoiceTaxTotal += $item->tax_amount;
                }

                $invoiceTotal = round($invoiceSubtotal + $invoiceTaxTotal, 2);

                $invoice->update([
                    'subtotal' => round($invoiceSubtotal, 2),
                    'tax_total' => round($invoiceTaxTotal, 2),
                    'total' => $invoiceTotal,
                ]);

                // Create Journal Entry for Invoice (Sales)
                // Find the fiscal year that contains this invoice date
                $fiscalYear = $fiscalYears->first(fn($fy) => $fy->containsDate($invoiceDate));
                
                if ($salesJournal && $fiscalYear) {
                    $entryNum = sprintf('JE-%s-%04d', now()->format('Ym'), $entryNumber++);
                    
                    $journalEntry = JournalEntry::create([
                        'owned_company_id' => $company->id,
                        'journal_id' => $salesJournal->id,
                        'fiscal_year_id' => $fiscalYear->id,
                        'entry_number' => $entryNum,
                        'entry_date' => $invoiceDate,
                        'reference' => $invoice->number,
                        'description' => "Sales Invoice {$invoice->number} - {$customer->name}",
                        'source_type' => Invoice::class,
                        'source_id' => $invoice->id,
                        'currency_code' => $company->currency_code ?? 'USD',
                        'exchange_rate' => 1,
                        'status' => JournalEntry::STATUS_POSTED,
                        'posted_at' => $invoiceDate,
                        'posted_by' => $userId,
                        'created_by' => $userId,
                    ]);

                    // Debit: Accounts Receivable
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $receivableAccount->id,
                        'description' => "Invoice {$invoice->number}",
                        'debit' => $invoiceTotal,
                        'credit' => 0,
                        'debit_base' => $invoiceTotal,
                        'credit_base' => 0,
                    ]);

                    // Credit: Revenue
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $revenueAccount->id,
                        'description' => "Sales revenue",
                        'debit' => 0,
                        'credit' => round($invoiceSubtotal, 2),
                        'debit_base' => 0,
                        'credit_base' => round($invoiceSubtotal, 2),
                    ]);

                    // Credit: Tax Payable (if applicable)
                    if ($invoiceTaxTotal > 0 && $taxAccount) {
                        JournalEntryLine::create([
                            'journal_entry_id' => $journalEntry->id,
                            'account_id' => $taxAccount->id,
                            'description' => "Tax on sales",
                            'debit' => 0,
                            'credit' => round($invoiceTaxTotal, 2),
                            'debit_base' => 0,
                            'credit_base' => round($invoiceTaxTotal, 2),
                        ]);
                    }

                    $invoice->update(['journal_entry_id' => $journalEntry->id]);
                }

                // Create Payment(s)
                if ($willBePaid || $willBePartialPaid) {
                    $paymentDate = $invoiceDate->copy()->addDays(rand(5, 25));
                    $paymentAmount = $willBePaid ? $invoiceTotal : round($invoiceTotal * (rand(30, 70) / 100), 2);

                    $payment = Payment::create([
                        'invoice_id' => $invoice->id,
                        'amount' => $paymentAmount,
                        'paid_at' => $paymentDate,
                        'reference' => 'PAY-' . strtoupper(substr(md5(rand()), 0, 8)),
                        'notes' => null,
                    ]);

                    // Create Journal Entry for Payment
                    $paymentFiscalYear = $fiscalYears->first(fn($fy) => $fy->containsDate($paymentDate));
                    
                    if ($cashJournal && $paymentFiscalYear) {
                        $payEntryNum = sprintf('JE-%s-%04d', now()->format('Ym'), $entryNumber++);
                        
                        $paymentEntry = JournalEntry::create([
                            'owned_company_id' => $company->id,
                            'journal_id' => $cashJournal->id,
                            'fiscal_year_id' => $paymentFiscalYear->id,
                            'entry_number' => $payEntryNum,
                            'entry_date' => $paymentDate,
                            'reference' => $payment->reference,
                            'description' => "Payment received for {$invoice->number}",
                            'source_type' => Payment::class,
                            'source_id' => $payment->id,
                            'currency_code' => $company->currency_code ?? 'USD',
                            'exchange_rate' => 1,
                            'status' => JournalEntry::STATUS_POSTED,
                            'posted_at' => $paymentDate,
                            'posted_by' => $userId,
                            'created_by' => $userId,
                        ]);

                        // Debit: Bank/Cash
                        JournalEntryLine::create([
                            'journal_entry_id' => $paymentEntry->id,
                            'account_id' => $bankAccount->id,
                            'description' => "Payment {$payment->reference}",
                            'debit' => $paymentAmount,
                            'credit' => 0,
                            'debit_base' => $paymentAmount,
                            'credit_base' => 0,
                        ]);

                        // Credit: Accounts Receivable
                        JournalEntryLine::create([
                            'journal_entry_id' => $paymentEntry->id,
                            'account_id' => $receivableAccount->id,
                            'description' => "Payment applied to {$invoice->number}",
                            'debit' => 0,
                            'credit' => $paymentAmount,
                            'debit_base' => 0,
                            'credit_base' => $paymentAmount,
                        ]);
                    }
                }
            }
        }

        // Create some draft quotes (not converted to orders)
        for ($i = 0; $i < 3; $i++) {
            $customer = $customers[array_rand($customers)];
            $selectedProducts = $products->random(rand(1, 3));

            $quote = Quote::create([
                'customer_company_id' => $customer->id,
                'number' => sprintf('Q-%s-%04d', $company->id, $quoteNumber++),
                'status' => QuoteStatus::Draft,
                'valid_until' => now()->addDays(30),
                'notes' => 'Pending review',
                'currency_code' => $company->currency_code ?? 'USD',
            ]);

            $subtotal = 0;
            foreach ($selectedProducts as $product) {
                $qty = rand(1, 10);
                $price = rand(100, 300) + (rand(0, 99) / 100);
                $lineSubtotal = $qty * $price;
                
                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'product_id' => $product->id,
                    'description' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => round($lineSubtotal, 2),
                    'line_total' => round($lineSubtotal, 2),
                ]);
                $subtotal += $lineSubtotal;
            }

            $quote->update([
                'subtotal' => round($subtotal, 2),
                'total' => round($subtotal, 2),
            ]);
        }

        $this->command->info("  Created quotes, orders, invoices with journal entries");
    }
}
