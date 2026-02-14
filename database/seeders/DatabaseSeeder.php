<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoTeamMembersSeeder::class,
            EditCustomersPermissionSeeder::class,
            BlogPermissionSeeder::class,
            InventoryPermissionSeeder::class,
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
