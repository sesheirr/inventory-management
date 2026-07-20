<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Create Admin User
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@inventory.local',
            'password' => bcrypt('admin123'),
            'role' => 'administrator',
        ]);

        // Create Regular User
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $this->call(ProductSeeder::class);
    }
}
