<?php

namespace Database\Seeders;

use App\Models\Accounting\CountryConfig;
use App\Models\Accounting\CountryTaxRate;
use Illuminate\Database\Seeder;

class AccountingCountryConfigSeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            [
                'country_code' => 'US',
                'name' => 'United States',
                'default_currency' => 'USD',
                'accounting_standard' => 'GAAP',
                'tax_name' => 'Sales Tax',
                'tax_rates' => [
                    // US has state-level sales tax, these are examples
                    ['name' => 'No Tax', 'code' => 'NOTAX', 'rate' => 0, 'category' => 'exempt'],
                ],
            ],
            [
                'country_code' => 'GB',
                'name' => 'United Kingdom',
                'default_currency' => 'GBP',
                'accounting_standard' => 'UK_GAAP',
                'tax_name' => 'VAT',
                'tax_rates' => [
                    ['name' => 'Standard Rate', 'code' => 'VAT20', 'rate' => 20, 'category' => 'standard'],
                    ['name' => 'Reduced Rate', 'code' => 'VAT5', 'rate' => 5, 'category' => 'reduced'],
                    ['name' => 'Zero Rate', 'code' => 'VAT0', 'rate' => 0, 'category' => 'zero'],
                    ['name' => 'Exempt', 'code' => 'VATEX', 'rate' => 0, 'category' => 'exempt'],
                ],
            ],
            [
                'country_code' => 'FR',
                'name' => 'France',
                'default_currency' => 'EUR',
                'accounting_standard' => 'PCG',
                'tax_name' => 'TVA',
                'tax_rates' => [
                    ['name' => 'Taux normal', 'code' => 'TVA20', 'rate' => 20, 'category' => 'standard'],
                    ['name' => 'Taux intermédiaire', 'code' => 'TVA10', 'rate' => 10, 'category' => 'reduced'],
                    ['name' => 'Taux réduit', 'code' => 'TVA55', 'rate' => 5.5, 'category' => 'reduced'],
                    ['name' => 'Taux particulier', 'code' => 'TVA21', 'rate' => 2.1, 'category' => 'reduced'],
                    ['name' => 'Exonéré', 'code' => 'TVAEX', 'rate' => 0, 'category' => 'exempt'],
                ],
            ],
            [
                'country_code' => 'DE',
                'name' => 'Germany',
                'default_currency' => 'EUR',
                'accounting_standard' => 'HGB',
                'tax_name' => 'MwSt',
                'tax_rates' => [
                    ['name' => 'Normaler Steuersatz', 'code' => 'MWST19', 'rate' => 19, 'category' => 'standard'],
                    ['name' => 'Ermäßigter Steuersatz', 'code' => 'MWST7', 'rate' => 7, 'category' => 'reduced'],
                    ['name' => 'Steuerfrei', 'code' => 'MWST0', 'rate' => 0, 'category' => 'exempt'],
                ],
            ],
            [
                'country_code' => 'CA',
                'name' => 'Canada',
                'default_currency' => 'CAD',
                'accounting_standard' => 'IFRS',
                'tax_name' => 'GST/HST',
                'tax_rates' => [
                    ['name' => 'GST', 'code' => 'GST5', 'rate' => 5, 'category' => 'standard'],
                    ['name' => 'HST (Ontario)', 'code' => 'HST13', 'rate' => 13, 'category' => 'standard'],
                    ['name' => 'Exempt', 'code' => 'GSTEX', 'rate' => 0, 'category' => 'exempt'],
                ],
            ],
            [
                'country_code' => 'AU',
                'name' => 'Australia',
                'default_currency' => 'AUD',
                'accounting_standard' => 'IFRS',
                'tax_name' => 'GST',
                'tax_rates' => [
                    ['name' => 'GST', 'code' => 'GST10', 'rate' => 10, 'category' => 'standard'],
                    ['name' => 'GST Free', 'code' => 'GSTF', 'rate' => 0, 'category' => 'zero'],
                    ['name' => 'Input Taxed', 'code' => 'GSTIT', 'rate' => 0, 'category' => 'exempt'],
                ],
            ],
        ];

        foreach ($countries as $countryData) {
            $taxRates = $countryData['tax_rates'] ?? [];
            unset($countryData['tax_rates']);

            $country = CountryConfig::firstOrCreate(
                ['country_code' => $countryData['country_code']],
                $countryData
            );

            foreach ($taxRates as $taxRate) {
                CountryTaxRate::firstOrCreate(
                    [
                        'country_code' => $country->country_code,
                        'code' => $taxRate['code'],
                    ],
                    array_merge($taxRate, [
                        'valid_from' => '2020-01-01',
                    ])
                );
            }
        }
    }
}
