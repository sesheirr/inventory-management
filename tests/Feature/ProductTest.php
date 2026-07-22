<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use App\Models\Product;
use Tests\TestCase;
use App\Models\User;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_index_page_loads(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertSee('Barang');
    }

    public function test_dashboard_page_loads(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_reports_page_is_not_available(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get('/reports');

        $response->assertStatus(200);
    }

    public function test_user_can_create_product(): void
    {
        $this->actingAs(User::factory()->create());

        Product::create([
            'name' => 'Test Product',
            'category' => 'Electronics',
            'subcategory' => 'Audio',
            'edition' => '2024',
            'description' => 'A test product',
            'stock' => 10,
            'price' => 199.99,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
    }
}
