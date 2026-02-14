<?php

namespace Database\Seeders;

use App\Models\Accounting\Account;
use App\Models\OwnedCompany;
use Illuminate\Database\Seeder;

class AccountingChartOfAccountsSeeder extends Seeder
{
    /**
     * Default Chart of Accounts template (generic/international)
     */
    protected array $defaultAccounts = [
        // Assets (1xxx)
        ['code' => '1000', 'name' => 'Assets', 'type' => 'asset', 'is_system' => true, 'children' => [
            ['code' => '1100', 'name' => 'Current Assets', 'type' => 'asset', 'subtype' => 'current_asset', 'children' => [
                ['code' => '1110', 'name' => 'Cash', 'type' => 'asset', 'subtype' => 'cash', 'is_system' => true],
                ['code' => '1120', 'name' => 'Bank Accounts', 'type' => 'asset', 'subtype' => 'bank', 'is_system' => true],
                ['code' => '1130', 'name' => 'Accounts Receivable', 'type' => 'asset', 'subtype' => 'receivable', 'is_system' => true],
                ['code' => '1140', 'name' => 'Inventory', 'type' => 'asset', 'subtype' => 'inventory'],
                ['code' => '1150', 'name' => 'Prepaid Expenses', 'type' => 'asset', 'subtype' => 'prepaid'],
            ]],
            ['code' => '1500', 'name' => 'Fixed Assets', 'type' => 'asset', 'subtype' => 'fixed_asset', 'children' => [
                ['code' => '1510', 'name' => 'Equipment', 'type' => 'asset', 'subtype' => 'fixed_asset'],
                ['code' => '1520', 'name' => 'Furniture & Fixtures', 'type' => 'asset', 'subtype' => 'fixed_asset'],
                ['code' => '1530', 'name' => 'Vehicles', 'type' => 'asset', 'subtype' => 'fixed_asset'],
                ['code' => '1540', 'name' => 'Buildings', 'type' => 'asset', 'subtype' => 'fixed_asset'],
                ['code' => '1550', 'name' => 'Land', 'type' => 'asset', 'subtype' => 'fixed_asset'],
                ['code' => '1590', 'name' => 'Accumulated Depreciation', 'type' => 'asset', 'subtype' => 'fixed_asset'],
            ]],
        ]],

        // Liabilities (2xxx)
        ['code' => '2000', 'name' => 'Liabilities', 'type' => 'liability', 'is_system' => true, 'children' => [
            ['code' => '2100', 'name' => 'Current Liabilities', 'type' => 'liability', 'subtype' => 'current_liability', 'children' => [
                ['code' => '2110', 'name' => 'Accounts Payable', 'type' => 'liability', 'subtype' => 'payable', 'is_system' => true],
                ['code' => '2120', 'name' => 'Accrued Expenses', 'type' => 'liability', 'subtype' => 'accrued'],
                ['code' => '2130', 'name' => 'Tax Payable', 'type' => 'liability', 'subtype' => 'tax_payable', 'is_system' => true],
                ['code' => '2140', 'name' => 'VAT/GST Payable', 'type' => 'liability', 'subtype' => 'tax_payable', 'is_system' => true],
                ['code' => '2150', 'name' => 'Wages Payable', 'type' => 'liability', 'subtype' => 'payable'],
                ['code' => '2160', 'name' => 'Unearned Revenue', 'type' => 'liability', 'subtype' => 'current_liability'],
            ]],
            ['code' => '2500', 'name' => 'Long-term Liabilities', 'type' => 'liability', 'subtype' => 'long_term_liability', 'children' => [
                ['code' => '2510', 'name' => 'Bank Loans', 'type' => 'liability', 'subtype' => 'long_term_liability'],
                ['code' => '2520', 'name' => 'Mortgage Payable', 'type' => 'liability', 'subtype' => 'long_term_liability'],
            ]],
        ]],

        // Equity (3xxx)
        ['code' => '3000', 'name' => 'Equity', 'type' => 'equity', 'is_system' => true, 'children' => [
            ['code' => '3100', 'name' => 'Owner\'s Capital', 'type' => 'equity', 'subtype' => 'capital', 'is_system' => true],
            ['code' => '3200', 'name' => 'Retained Earnings', 'type' => 'equity', 'subtype' => 'retained_earnings', 'is_system' => true],
            ['code' => '3300', 'name' => 'Owner\'s Drawings', 'type' => 'equity', 'subtype' => 'drawing'],
        ]],

        // Revenue (4xxx)
        ['code' => '4000', 'name' => 'Revenue', 'type' => 'revenue', 'is_system' => true, 'children' => [
            ['code' => '4100', 'name' => 'Sales Revenue', 'type' => 'revenue', 'subtype' => 'sales', 'is_system' => true],
            ['code' => '4200', 'name' => 'Service Revenue', 'type' => 'revenue', 'subtype' => 'operating_revenue'],
            ['code' => '4300', 'name' => 'Interest Income', 'type' => 'revenue', 'subtype' => 'other_revenue'],
            ['code' => '4400', 'name' => 'Other Income', 'type' => 'revenue', 'subtype' => 'other_revenue'],
            ['code' => '4500', 'name' => 'Sales Returns & Allowances', 'type' => 'revenue', 'subtype' => 'sales'],
            ['code' => '4600', 'name' => 'Sales Discounts', 'type' => 'revenue', 'subtype' => 'sales'],
        ]],

        // Expenses (5xxx-6xxx)
        ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'expense', 'subtype' => 'cost_of_goods', 'is_system' => true, 'children' => [
            ['code' => '5100', 'name' => 'Purchases', 'type' => 'expense', 'subtype' => 'cost_of_goods'],
            ['code' => '5200', 'name' => 'Purchase Returns & Allowances', 'type' => 'expense', 'subtype' => 'cost_of_goods'],
            ['code' => '5300', 'name' => 'Purchase Discounts', 'type' => 'expense', 'subtype' => 'cost_of_goods'],
            ['code' => '5400', 'name' => 'Freight In', 'type' => 'expense', 'subtype' => 'cost_of_goods'],
        ]],

        ['code' => '6000', 'name' => 'Operating Expenses', 'type' => 'expense', 'subtype' => 'operating_expense', 'is_system' => true, 'children' => [
            ['code' => '6100', 'name' => 'Salaries & Wages', 'type' => 'expense', 'subtype' => 'payroll'],
            ['code' => '6110', 'name' => 'Payroll Taxes', 'type' => 'expense', 'subtype' => 'payroll'],
            ['code' => '6120', 'name' => 'Employee Benefits', 'type' => 'expense', 'subtype' => 'payroll'],
            ['code' => '6200', 'name' => 'Rent Expense', 'type' => 'expense', 'subtype' => 'operating_expense'],
            ['code' => '6210', 'name' => 'Utilities', 'type' => 'expense', 'subtype' => 'operating_expense'],
            ['code' => '6220', 'name' => 'Insurance', 'type' => 'expense', 'subtype' => 'operating_expense'],
            ['code' => '6300', 'name' => 'Office Supplies', 'type' => 'expense', 'subtype' => 'operating_expense'],
            ['code' => '6310', 'name' => 'Postage & Shipping', 'type' => 'expense', 'subtype' => 'operating_expense'],
            ['code' => '6400', 'name' => 'Advertising & Marketing', 'type' => 'expense', 'subtype' => 'operating_expense'],
            ['code' => '6500', 'name' => 'Professional Fees', 'type' => 'expense', 'subtype' => 'operating_expense'],
            ['code' => '6510', 'name' => 'Legal Fees', 'type' => 'expense', 'subtype' => 'operating_expense'],
            ['code' => '6520', 'name' => 'Accounting Fees', 'type' => 'expense', 'subtype' => 'operating_expense'],
            ['code' => '6600', 'name' => 'Depreciation Expense', 'type' => 'expense', 'subtype' => 'depreciation'],
            ['code' => '6700', 'name' => 'Bank Fees', 'type' => 'expense', 'subtype' => 'operating_expense'],
            ['code' => '6800', 'name' => 'Interest Expense', 'type' => 'expense', 'subtype' => 'other_expense'],
            ['code' => '6900', 'name' => 'Miscellaneous Expense', 'type' => 'expense', 'subtype' => 'other_expense'],
        ]],
    ];

    public function run(): void
    {
        // Create default COA for each owned company
        $companies = OwnedCompany::all();

        foreach ($companies as $company) {
            // Skip if company already has accounts
            if ($company->accounts()->exists()) {
                continue;
            }

            $this->createAccounts($company->id, $this->defaultAccounts);
        }
    }

    protected function createAccounts(int $companyId, array $accounts, ?int $parentId = null): void
    {
        foreach ($accounts as $accountData) {
            $children = $accountData['children'] ?? [];
            unset($accountData['children']);

            $account = Account::create(array_merge($accountData, [
                'owned_company_id' => $companyId,
                'parent_id' => $parentId,
                'is_system' => $accountData['is_system'] ?? false,
            ]));

            if (!empty($children)) {
                $this->createAccounts($companyId, $children, $account->id);
            }
        }
    }

    /**
     * Create default COA for a specific company (can be called from a service)
     */
    public static function createForCompany(int $companyId): void
    {
        $seeder = new self();
        $seeder->createAccounts($companyId, $seeder->defaultAccounts);
    }
}
