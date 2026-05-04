<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Main Database Seeder
 * 
 * Calls all other seeders in the correct order.
 * Creates a demo user and runs RolesAndPermissionsSeeder + TemplateSeeder.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo user
        User::create([
            'name'              => 'Demo User',
            'email'             => 'demo@example.com',
            'password'          => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // Run seeders
        $this->call([
            RolesAndPermissionsSeeder::class,
            TemplateSeeder::class,
        ]);
    }
}