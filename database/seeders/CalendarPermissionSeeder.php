<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class CalendarPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create calendar permissions
        $viewCalendar = Permission::firstOrCreate(
            ['slug' => 'view.calendar'],
            ['name' => 'View Calendar']
        );

        $editCalendar = Permission::firstOrCreate(
            ['slug' => 'edit.calendar'],
            ['name' => 'Edit Calendar']
        );

        // Assign to admin
        $admin = Role::where('slug', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching([
                $viewCalendar->id,
                $editCalendar->id,
            ]);
        }

        // By default, all authenticated users can access calendar
        // Create a role for restricted calendar access if needed later
    }
}
