<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['active', 'inactive', 'out_of_stock'];

        for ($i = 1; $i <= 50; $i++) {
            Product::create([
                'name' => 'Product ' . $i,
                'category' => $this->categories()[($i - 1) % count($this->categories())],
                'subcategory' => $this->subcategories()[($i - 1) % count($this->subcategories())],
                'edition' => 'Edition ' . (($i % 5) + 1),
                'description' => 'Premium inventory item crafted for modern store operations.',
                'stock' => rand(5, 120),
                'price' => round(rand(1000, 5000) / 10, 2),
                'status' => $statuses[array_rand($statuses)],
                'image' => null,
            ]);
        }
    }

    private function categories(): array
    {
        return ['Electronics', 'Furniture', 'Fashion', 'Sports', 'Home'];
    }

    private function subcategories(): array
    {
        return ['Audio', 'Office', 'Accessories', 'Outdoor', 'Lighting'];
    }
}
