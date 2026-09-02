<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@web.inventaris'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('inventarisdiskominfo'),
                'role' => 'superadmin',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'game.fauzann@gmail.com'],
            [
                'name' => 'Fauzan Super Admin',
                'password' => Hash::make('123456zz'),
                'role' => 'superadmin',
                'email_verified_at' => now(),
            ]
        );
    }
}
