<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoEmployeesSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'password';

    private const DEMO_EMPLOYEES = [
        ['name' => 'Admin One', 'email' => 'admin1@demo.test'],
        ['name' => 'Admin Two', 'email' => 'admin2@demo.test'],
        ['name' => 'Demo Employee One', 'email' => 'user1@demo.test'],
        ['name' => 'Demo Employee Two', 'email' => 'user2@demo.test'],
        ['name' => 'Demo Employee Three', 'email' => 'user3@demo.test'],
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
        $employeeIds = [];

        foreach (self::DEMO_EMPLOYEES as $employee) {
            $employeeIds[] = DB::table('employees')->insertGetId([
                'name' => $employee['name'],
                'email' => $employee['email'],
                'password' => $password,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([0, 1] as $index) {
            DB::table('role_employee')->insert([
                'role_id' => $adminRoleId,
                'employee_id' => $employeeIds[$index],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
