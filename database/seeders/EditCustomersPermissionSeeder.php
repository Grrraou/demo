<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EditCustomersPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $permission = DB::table('permissions')->where('slug', 'edit.customers')->first();
        if (! $permission) {
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
            DB::table('permission_role')->insert([
                'permission_id' => $permissionId,
                'role_id' => $adminRole->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $customerEditorRole = DB::table('roles')->where('slug', 'customer-editor')->first();
        if (! $customerEditorRole) {
            $customerEditorRoleId = DB::table('roles')->insertGetId([
                'name' => 'Customer editor',
                'slug' => 'customer-editor',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('permission_role')->insert([
                'permission_id' => $permissionId,
                'role_id' => $customerEditorRoleId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } elseif (! DB::table('permission_role')->where('permission_id', $permissionId)->where('role_id', $customerEditorRole->id)->exists()) {
            DB::table('permission_role')->insert([
                'permission_id' => $permissionId,
                'role_id' => $customerEditorRole->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}

