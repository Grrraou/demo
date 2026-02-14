<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class CustomersPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create view.customers permission (edit.customers already exists from EditCustomersPermissionSeeder)
        $viewCustomers = Permission::firstOrCreate(
            ['slug' => 'view.customers'],
            ['name' => 'View Customers']
        );

        // Ensure edit.customers exists
        $editCustomers = Permission::firstOrCreate(
            ['slug' => 'edit.customers'],
            ['name' => 'Edit Customers']
        );

        // Create leads permissions
        $viewLeads = Permission::firstOrCreate(
            ['slug' => 'view.leads'],
            ['name' => 'View Leads']
        );

        $editLeads = Permission::firstOrCreate(
            ['slug' => 'edit.leads'],
            ['name' => 'Edit Leads']
        );

        // Get or create customer-editor role
        $customerEditor = Role::firstOrCreate(
            ['slug' => 'customer-editor'],
            ['name' => 'Customer Editor']
        );

        // Assign all customer permissions to customer-editor
        $customerEditor->permissions()->syncWithoutDetaching([
            $viewCustomers->id,
            $editCustomers->id,
            $viewLeads->id,
            $editLeads->id,
        ]);

        // Create customer-viewer role (view only)
        $customerViewer = Role::firstOrCreate(
            ['slug' => 'customer-viewer'],
            ['name' => 'Customer Viewer']
        );

        $customerViewer->permissions()->syncWithoutDetaching([
            $viewCustomers->id,
            $viewLeads->id,
        ]);

        // Create leads-editor role (leads only)
        $leadsEditor = Role::firstOrCreate(
            ['slug' => 'leads-editor'],
            ['name' => 'Leads Editor']
        );

        $leadsEditor->permissions()->syncWithoutDetaching([
            $viewLeads->id,
            $editLeads->id,
        ]);

        // Assign all permissions to admin
        $admin = Role::where('slug', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching([
                $viewCustomers->id,
                $editCustomers->id,
                $viewLeads->id,
                $editLeads->id,
            ]);
        }
    }
}
