<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

/**
 * Roles & Permissions Seeder
 * 
 * Creates default roles (admin, manager, user) and permissions.
 * Assigns all permissions to admin, limited to manager, basic to user.
 * Creates a default admin account if not exists.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all available permissions
        $permissions = [
            // User Management
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'manage-users',
            
            // Report Management
            'view-reports',
            'create-reports',
            'edit-reports',
            'delete-reports',
            'manage-reports',
            'assign-reports',
            
            // Task Management
            'view-tasks',
            'create-tasks',
            'edit-tasks',
            'delete-tasks',
            'manage-tasks',
            
            // Template Management
            'view-templates',
            'create-templates',
            'edit-templates',
            'delete-templates',
            'manage-templates',
            
            // System Settings
            'manage-settings',
            'view-analytics',
            'manage-roles',
            'view-activities',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $managerRole = Role::create(['name' => 'manager']);
        $userRole = Role::create(['name' => 'user']);

        // Assign ALL permissions to admin
        $adminRole->givePermissionTo(Permission::all());

        // Assign limited permissions to manager
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
            'view-activities',
        ]);

        // Assign basic permissions to regular users
        $userRole->givePermissionTo([
            'view-reports',
            'create-reports',
            'edit-reports',
            'view-tasks',
            'view-templates',
        ]);

        // Create default admin user if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'              => 'Admin User',
                'password'          => bcrypt('password'),
                'is_admin'          => true,
                'is_premium'        => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // Create default manager user if not exists
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name'              => 'Manager User',
                'password'          => bcrypt('password'),
                'is_premium'        => true,
                'email_verified_at' => now(),
            ]
        );
        $manager->assignRole('manager');

        // Create default regular user if not exists
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name'              => 'Regular User',
                'password'          => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $user->assignRole('user');

        // Output success message
        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Manager: manager@example.com / password');
        $this->command->info('User: user@example.com / password');
    }
}