<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCreateFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_product_without_category_id_and_without_image(): void
    {
        Category::create(['name' => 'Elektronik']);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'Router 5G',
            'category' => 'Elektronik',
            'subcategory' => 'Networking',
            'room' => 'Ruang Server',
            'stock' => 3,
            'status' => 'active',
            'description' => 'Router baru',
        ]);

        $response->assertRedirect('/products');
        $this->assertDatabaseHas('products', ['name' => 'Router 5G', 'category' => 'Elektronik']);
        $this->assertDatabaseHas('products', ['name' => 'Router 5G', 'room' => 'Ruang Server']);
    }
}
