<?php

namespace Database\Seeders;

use App\Models\CustomerCompany;
use App\Models\CustomerContact;
use App\Models\OwnedCompany;
use Illuminate\Database\Seeder;

class CustomerCompaniesSeeder extends Seeder
{
    public function run(): void
    {
        if (CustomerCompany::query()->exists()) {
            return;
        }

        $ownedCompanyIds = OwnedCompany::query()->pluck('id')->toArray();
        if (empty($ownedCompanyIds)) {
            return;
        }

        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 50; $i++) {
            $company = CustomerCompany::query()->create([
                'name' => $faker->company(),
                'email' => $faker->optional(0.7)->companyEmail(),
                'phone' => $faker->optional(0.5)->phoneNumber(),
                'address' => $faker->optional(0.4)->address(),
            ]);

            // Link to 1–3 owned companies at random
            $count = (int) $faker->numberBetween(1, min(3, count($ownedCompanyIds)));
            $selected = $faker->randomElements($ownedCompanyIds, $count);
            $company->ownedCompanies()->attach($selected);

            // 3–20 contacts per customer company
            $contactCount = (int) $faker->numberBetween(3, 20);
            for ($c = 0; $c < $contactCount; $c++) {
                CustomerContact::query()->create([
                    'customer_company_id' => $company->id,
                    'name' => $faker->name(),
                    'email' => $faker->safeEmail(),
                    'phone' => $faker->optional(0.6)->phoneNumber(),
                    'job_title' => $faker->optional(0.8)->jobTitle(),
                ]);
            }
        }
    }
}
