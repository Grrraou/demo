<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\OwnedCompany;
use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        if (Lead::query()->exists()) {
            return;
        }

        $ownedCompanies = OwnedCompany::query()->get();
        if ($ownedCompanies->isEmpty()) {
            return;
        }

        $teamMemberIds = TeamMember::query()->pluck('id')->toArray();
        $faker = \Faker\Factory::create();

        // Statuses with weighted distribution (more leads in early stages)
        $statuses = [
            Lead::STATUS_NEW => 25,
            Lead::STATUS_CONTACTED => 20,
            Lead::STATUS_QUALIFIED => 15,
            Lead::STATUS_PROPOSAL => 12,
            Lead::STATUS_NEGOTIATION => 10,
            Lead::STATUS_WON => 10,
            Lead::STATUS_LOST => 8,
        ];

        foreach ($ownedCompanies as $ownedCompany) {
            // Create 15-30 leads per owned company
            $leadCount = $faker->numberBetween(15, 30);
            $positionsByStatus = [];

            for ($i = 0; $i < $leadCount; $i++) {
                // Pick status based on weighted distribution
                $status = $this->weightedRandom($statuses);
                
                // Track position per status
                if (!isset($positionsByStatus[$status])) {
                    $positionsByStatus[$status] = 0;
                }

                Lead::query()->create([
                    'owned_company_id' => $ownedCompany->id,
                    'name' => $faker->name(),
                    'email' => $faker->optional(0.8)->safeEmail(),
                    'phone' => $faker->optional(0.6)->phoneNumber(),
                    'company_name' => $faker->optional(0.7)->company(),
                    'status' => $status,
                    'position' => $positionsByStatus[$status]++,
                    'value' => $faker->optional(0.6)->randomFloat(2, 500, 50000),
                    'notes' => $faker->optional(0.4)->sentence(),
                    'assigned_to' => $faker->optional(0.5)->randomElement($teamMemberIds),
                    'created_by' => $faker->randomElement($teamMemberIds),
                    'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                ]);
            }
        }
    }

    private function weightedRandom(array $weights): string
    {
        $total = array_sum($weights);
        $rand = mt_rand(1, $total);
        
        foreach ($weights as $key => $weight) {
            $rand -= $weight;
            if ($rand <= 0) {
                return $key;
            }
        }
        
        return array_key_first($weights);
    }
}
