<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EditCustomersPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permission = DB::table('permissions')->where('slug', 'edit.customers')->first();
        if (! $permission) {
            $now = now();
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => 'Edit customers',
                'slug' => 'edit.customers',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $permissionId = $permission->id;
        }

        $adminRole = DB::table('roles')->where('slug', 'admin')->first();
        if ($adminRole && ! DB::table('permission_role')->where('permission_id', $permissionId)->where('role_id', $adminRole->id)->exists()) {
            $now = now();
            DB::table('permission_role')->insert([
                'permission_id' => $permissionId,
                'role_id' => $adminRole->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}

