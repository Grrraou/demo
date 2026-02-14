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
            // Data seeders
            OwnedCompaniesSeeder::class,
            CustomerCompaniesSeeder::class,
            BlogArticlesSeeder::class,
            InventorySeeder::class,
            SalesSeeder::class,
            LeadSeeder::class,
            EventSeeder::class,
        ]);
    }
}
