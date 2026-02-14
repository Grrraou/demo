<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class SalesPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $viewSales = Permission::firstOrCreate(
            ['slug' => 'view.sales'],
            ['name' => 'View Sales']
        );

        $editSales = Permission::firstOrCreate(
            ['slug' => 'edit.sales'],
            ['name' => 'Edit Sales']
        );

        // Create sales-editor role
        $salesEditor = Role::firstOrCreate(
            ['slug' => 'sales-editor'],
            ['name' => 'Sales Editor']
        );

        // Assign permissions to sales-editor
        $salesEditor->permissions()->syncWithoutDetaching([
            $viewSales->id,
            $editSales->id,
        ]);

        // Assign permissions to admin
        $admin = Role::where('slug', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching([
                $viewSales->id,
                $editSales->id,
            ]);
        }

        // Create sales-viewer role (view only)
        $salesViewer = Role::firstOrCreate(
            ['slug' => 'sales-viewer'],
            ['name' => 'Sales Viewer']
        );

        $salesViewer->permissions()->syncWithoutDetaching([
            $viewSales->id,
        ]);
    }
}
