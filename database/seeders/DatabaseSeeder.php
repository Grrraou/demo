<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoTeamMembersSeeder::class,
            // Permission seeders
            EditCustomersPermissionSeeder::class,
            CustomersPermissionSeeder::class,
            BlogPermissionSeeder::class,
            InventoryPermissionSeeder::class,
            SalesPermissionSeeder::class,
            CalendarPermissionSeeder::class,
            AccountingPermissionSeeder::class,
            // Accounting data seeders
            AccountingCurrencySeeder::class,
            AccountingCountryConfigSeeder::class,
            // Data seeders
            OwnedCompaniesSeeder::class,
            CustomerCompaniesSeeder::class,
            BlogArticlesSeeder::class,
            InventorySeeder::class,
            SalesSeeder::class,
            LeadSeeder::class,
            EventSeeder::class,
            // Combined Accounting + Sales data seeder
            AccountingDataSeeder::class,
        ]);
    }
}
