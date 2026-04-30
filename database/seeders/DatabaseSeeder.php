<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'demo',
            'email' => 'demo@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->call([
            TemplateSeeder::class,
        ]);
    }
}