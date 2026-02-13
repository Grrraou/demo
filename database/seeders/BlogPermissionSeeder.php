<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BlogPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $createId = $this->ensurePermission('Create articles', 'create.articles', $now);
        $editId = $this->ensurePermission('Edit articles', 'edit.articles', $now);

        $adminRole = DB::table('roles')->where('slug', 'admin')->first();
        if ($adminRole) {
            $this->attachPermissionToRole($adminRole->id, $createId, $now);
            $this->attachPermissionToRole($adminRole->id, $editId, $now);
        }

        $blogEditorRole = DB::table('roles')->where('slug', 'blog-editor')->first();
        if (! $blogEditorRole) {
            $blogEditorRoleId = DB::table('roles')->insertGetId([
                'name' => 'Blog editor',
                'slug' => 'blog-editor',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->attachPermissionToRole($blogEditorRoleId, $createId, $now);
            $this->attachPermissionToRole($blogEditorRoleId, $editId, $now);
        } else {
            $this->attachPermissionToRole($blogEditorRole->id, $createId, $now);
            $this->attachPermissionToRole($blogEditorRole->id, $editId, $now);
        }
    }

    private function ensurePermission(string $name, string $slug, $now): int
    {
        $p = DB::table('permissions')->where('slug', $slug)->first();
        if ($p) {
            return $p->id;
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
