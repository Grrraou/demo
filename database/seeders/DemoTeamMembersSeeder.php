<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoTeamMembersSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'password';

    private const DEMO_TEAM_MEMBERS = [
        ['name' => 'Admin One', 'email' => 'admin1@demo.test'],
        ['name' => 'Admin Two', 'email' => 'admin2@demo.test'],
        ['name' => 'Demo Team Member One', 'email' => 'user1@demo.test'],
        ['name' => 'Demo Team Member Two', 'email' => 'user2@demo.test'],
        ['name' => 'Demo Team Member Three', 'email' => 'user3@demo.test'],
    ];

    public function run(): void
    {
        if (DB::table('roles')->where('slug', 'admin')->exists()) {
            return;
        }

        $now = now();

        $adminRoleId = DB::table('roles')->insertGetId([
            'name' => 'Admin',
            'slug' => 'admin',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $permissionId = DB::table('permissions')->insertGetId([
            'name' => 'Access admin',
            'slug' => 'access.admin',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => $permissionId,
            'role_id' => $adminRoleId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $password = Hash::make(self::DEMO_PASSWORD);
        $teamMemberIds = [];

        foreach (self::DEMO_TEAM_MEMBERS as $member) {
            $teamMemberIds[] = DB::table('team_members')->insertGetId([
                'name' => $member['name'],
                'email' => $member['email'],
                'password' => $password,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([0, 1] as $index) {
            DB::table('role_team_member')->insert([
                'role_id' => $adminRoleId,
                'team_member_id' => $teamMemberIds[$index],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
