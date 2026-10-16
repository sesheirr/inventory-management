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
        // Create Admin User (Preserved existing credential)
        User::updateOrCreate(
            ['email' => 'admin@inventory.local'],
            [
                'name' => 'Administrator Inventaris',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'phone' => '081223344556',
                'gender' => 'Laki-laki',
                'address' => 'Jl. Pembangunan No. 181, Tarogong Kidul, Kabupaten Garut',
            ]
        );

        // Create Regular User (Preserved existing credential)
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Staf Operator Inventaris',
                'password' => bcrypt('password'),
                'role' => 'user',
                'phone' => '081398765432',
                'gender' => 'Perempuan',
                'address' => 'Kecamatan Garut Kota, Kabupaten Garut',
            ]
        );

        $this->call(SuperAdminSeeder::class);
        $this->call(ProductSeeder::class);
    }
}
