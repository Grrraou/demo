<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AccountingPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $viewAccounting = Permission::firstOrCreate(
            ['slug' => 'view.accounting'],
            ['name' => 'View Accounting']
        );

        $editAccounting = Permission::firstOrCreate(
            ['slug' => 'edit.accounting'],
            ['name' => 'Edit Accounting']
        );

        $closePeriods = Permission::firstOrCreate(
            ['slug' => 'close.periods'],
            ['name' => 'Close Fiscal Periods']
        );

        $adminAccounting = Permission::firstOrCreate(
            ['slug' => 'admin.accounting'],
            ['name' => 'Accounting Administration']
        );

        // Create accounting-viewer role
        $accountingViewer = Role::firstOrCreate(
            ['slug' => 'accounting-viewer'],
            ['name' => 'Accounting Viewer']
        );

        $accountingViewer->permissions()->syncWithoutDetaching([
            $viewAccounting->id,
        ]);

        // Create accounting-editor role
        $accountingEditor = Role::firstOrCreate(
            ['slug' => 'accounting-editor'],
            ['name' => 'Accounting Editor']
        );

        $accountingEditor->permissions()->syncWithoutDetaching([
            $viewAccounting->id,
            $editAccounting->id,
        ]);

        // Create accounting-manager role
        $accountingManager = Role::firstOrCreate(
            ['slug' => 'accounting-manager'],
            ['name' => 'Accounting Manager']
        );

        $accountingManager->permissions()->syncWithoutDetaching([
            $viewAccounting->id,
            $editAccounting->id,
            $closePeriods->id,
        ]);

        // Assign all accounting permissions to admin
        $admin = Role::where('slug', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching([
                $viewAccounting->id,
                $editAccounting->id,
                $closePeriods->id,
                $adminAccounting->id,
            ]);
        }
    }
}
