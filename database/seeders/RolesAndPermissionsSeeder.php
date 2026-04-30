<?php
// database/seeders/RolesAndPermissionsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'manage-users',
            
            // Report management
            'view-reports',
            'create-reports',
            'edit-reports',
            'delete-reports',
            'manage-reports',
            'assign-reports',
            
            // Task management
            'view-tasks',
            'create-tasks',
            'edit-tasks',
            'delete-tasks',
            'manage-tasks',
            
            // Template management
            'view-templates',
            'create-templates',
            'edit-templates',
            'delete-templates',
            
            // Settings
            'manage-settings',
            'view-analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $managerRole = Role::create(['name' => 'manager']);
        $userRole = Role::create(['name' => 'user']);

        // Assign all permissions to admin
        $adminRole->givePermissionTo(Permission::all());

        // Assign manager permissions
        $managerRole->givePermissionTo([
            'view-users',
            'view-reports',
            'create-reports',
            'edit-reports',
            'assign-reports',
            'view-tasks',
            'create-tasks',
            'edit-tasks',
            'manage-tasks',
            'view-templates',
            'view-analytics',
        ]);

        // Assign user permissions
        $userRole->givePermissionTo([
            'view-reports',
            'create-reports',
            'edit-reports',
            'view-tasks',
            'view-templates',
        ]);

        // Create admin user if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'is_admin' => true,
            ]
        );
        $admin->assignRole('admin');
    }
}