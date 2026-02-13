<?php

namespace Database\Factories;

use App\Models\CustomerCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerCompanyFactory extends Factory
{
    protected $model = CustomerCompany::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'email' => fake()->optional(0.7)->companyEmail(),
            'phone' => fake()->optional(0.5)->phoneNumber(),
            'address' => fake()->optional(0.4)->address(),
        ];
    }
}
