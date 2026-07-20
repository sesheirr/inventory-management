<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_index_page_loads(): void
    {
        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertSee('Products');
    }

    public function test_dashboard_page_loads(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_reports_page_is_not_available(): void
    {
        $response = $this->get('/reports');

        $response->assertNotFound();
    }

    public function test_user_can_create_product(): void
    {
        $response = $this->post('/products', [
            'name' => 'Test Product',
            'category' => 'Electronics',
            'subcategory' => 'Audio',
            'edition' => '2024',
            'description' => 'A test product',
            'stock' => 10,
            'price' => 199.99,
            'status' => 'active',
            'image' => UploadedFile::fake()->image('product.jpg'),
        ]);

        $response->assertRedirect('/products');
        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
    }
}
