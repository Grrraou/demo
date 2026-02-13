<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $viewId = $this->ensurePermission('View inventory', 'view.inventory', $now);
        $editId = $this->ensurePermission('Edit inventory', 'edit.inventory', $now);

        $adminRole = DB::table('roles')->where('slug', 'admin')->first();
        if ($adminRole) {
            $this->attachPermissionToRole($adminRole->id, $viewId, $now);
            $this->attachPermissionToRole($adminRole->id, $editId, $now);
        }

        $inventoryEditorRole = DB::table('roles')->where('slug', 'inventory-editor')->first();
        if (! $inventoryEditorRole) {
            $inventoryEditorRoleId = DB::table('roles')->insertGetId([
                'name' => 'Inventory editor',
                'slug' => 'inventory-editor',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->attachPermissionToRole($inventoryEditorRoleId, $viewId, $now);
            $this->attachPermissionToRole($inventoryEditorRoleId, $editId, $now);
        } else {
            $this->attachPermissionToRole($inventoryEditorRole->id, $viewId, $now);
            $this->attachPermissionToRole($inventoryEditorRole->id, $editId, $now);
        }
    }

    private function ensurePermission(string $name, string $slug, $now): int
    {
        $p = DB::table('permissions')->where('slug', $slug)->first();
        if ($p) {
            return (int) $p->id;
        }
        return DB::table('permissions')->insertGetId([
            'name' => $name,
            'slug' => $slug,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function attachPermissionToRole(int $roleId, int $permissionId, $now): void
    {
        if (DB::table('permission_role')->where('role_id', $roleId)->where('permission_id', $permissionId)->exists()) {
            return;
        }
        DB::table('permission_role')->insert([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
