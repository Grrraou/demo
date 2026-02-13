<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OwnedCompaniesSeeder extends Seeder
{
    private const DEMO_COMPANIES = [
        ['name' => 'Acme Corp', 'slug' => 'acme-corp', 'description' => 'Main demo company', 'color' => '#6366f1', 'logo' => '1-698e6945ae4bb.png'],
        ['name' => 'Globex Inc', 'slug' => 'globex-inc', 'description' => 'Secondary demo company', 'color' => '#059669', 'logo' => '2-698e695b4e774.png'],
        ['name' => 'Initech', 'slug' => 'initech', 'description' => 'Third demo company', 'color' => '#dc2626', 'logo' => '3-698e6969c0db0.png'],
    ];

    public function run(): void
    {
        if (DB::table('owned_companies')->exists()) {
            return;
        }

        $now = now();
        $companyIds = [];

        foreach (self::DEMO_COMPANIES as $company) {
            $companyIds[] = DB::table('owned_companies')->insertGetId([
                'name' => $company['name'],
                'slug' => $company['slug'],
                'description' => $company['description'],
                'color' => $company['color'],
                'logo' => $company['logo'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Attach companies to team members: first two (admins) get all 3 companies, others get first company only
        $teamMemberIds = DB::table('team_members')->pluck('id')->toArray();
        foreach ($teamMemberIds as $index => $teamMemberId) {
            $ids = $index < 2 ? $companyIds : [$companyIds[0]];
            foreach ($ids as $companyId) {
                DB::table('owned_company_team_member')->insert([
                    'team_member_id' => $teamMemberId,
                    'owned_company_id' => $companyId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
