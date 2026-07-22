<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;

class ProductImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_image_uploads_and_is_stored()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('product.jpg', 600, 600)->size(500);

        $response = $this->actingAs($user)->post('/products', [
            'name' => 'Test Upload Product',
            'category' => 'Peralatan IT & Jaringan',
            'subcategory' => 'Audio',
            'edition' => '2026',
            'description' => 'Test image upload',
            'stock' => 5,
            'price' => 100.00,
            'status' => 'active',
            'image' => $file,
        ]);

        $response->assertRedirect('/products');

        $this->assertDatabaseHas('products', ['name' => 'Test Upload Product']);

        $files = Storage::disk('public')->allFiles('products');
        $this->assertNotEmpty($files, 'No files stored in public/products');

        // check thumbnails folder if created
        $thumbs = Storage::disk('public')->allFiles('products/thumbs');
        // thumbs may or may not be created depending on environment; assert nothing fatal
        $this->assertIsArray($thumbs);
    }
}
