<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoEmployeesSeeder::class,
            EditCustomersPermissionSeeder::class,
            BlogPermissionSeeder::class,
            OwnedCompaniesSeeder::class,
            CustomerCompaniesSeeder::class,
            BlogArticlesSeeder::class,
        ]);
    }
}
