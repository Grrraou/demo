<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'password';

    private const DEMO_USERS = [
        ['name' => 'Admin One', 'email' => 'admin1@demo.test'],
        ['name' => 'Admin Two', 'email' => 'admin2@demo.test'],
        ['name' => 'Demo User One', 'email' => 'user1@demo.test'],
        ['name' => 'Demo User Two', 'email' => 'user2@demo.test'],
        ['name' => 'Demo User Three', 'email' => 'user3@demo.test'],
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
        $userIds = [];

        foreach (self::DEMO_USERS as $user) {
            $userIds[] = DB::table('users')->insertGetId([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => $password,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([0, 1] as $index) {
            DB::table('role_user')->insert([
                'role_id' => $adminRoleId,
                'user_id' => $userIds[$index],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
