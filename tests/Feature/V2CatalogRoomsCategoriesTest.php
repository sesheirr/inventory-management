<?php

/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Catalog, Rooms, Categories & Scanner Tests
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class V2CatalogRoomsCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_v2_catalog_rooms_categories_and_scanner_inertia_pages_render_and_carry_attribution(): void
    {
        $requiredPages = [
            'resources/js/pages/Products/Index.tsx',
            'resources/js/pages/Products/Form.tsx',
            'resources/js/pages/Products/Show.tsx',
            'resources/js/pages/Products/Scan.tsx',
            'resources/js/pages/Rooms/Index.tsx',
            'resources/js/pages/Rooms/Show.tsx',
            'resources/js/pages/Categories/Index.tsx',
            'resources/js/pages/Categories/Show.tsx',
        ];

        foreach ($requiredPages as $pagePath) {
            $full = base_path($pagePath);
            $this->assertFileExists($full, "Missing v2 page: {$pagePath}");
            $this->assertStringContainsString('fwzann-abd', (string) file_get_contents($full));
        }

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $category = Category::create(['name' => 'Server & Cloud']);
        $room = Room::create(['name' => 'Data Center Utama', 'code' => 'DC-01']);
        $product = Product::create([
            'name' => 'Dell PowerEdge R760',
            'kode_barang' => 'BRG-DELL01',
            'barcode' => 'INV-DELL01',
            'category_id' => $category->id,
            'room_id' => $room->id,
            'stock' => 2,
            'status' => 'active',
        ]);

        $this->actingAs($admin)->get('/products?v2=1')
            ->assertOk()
            ->assertSee('Products\\/Index', false);

        $this->actingAs($admin)->get('/products/create?v2=1')
            ->assertOk()
            ->assertSee('Products\\/Form', false);

        $this->actingAs($admin)->get("/products/{$product->id}?v2=1")
            ->assertOk()
            ->assertSee('Products\\/Show', false);

        $this->actingAs($admin)->get('/scan-barcode?v2=1')
            ->assertOk()
            ->assertSee('Products\\/Scan', false);

        $this->actingAs($admin)->get('/rooms?v2=1')
            ->assertOk()
            ->assertSee('Rooms\\/Index', false);

        $this->actingAs($admin)->get("/rooms/{$room->id}?v2=1")
            ->assertOk()
            ->assertSee('Rooms\\/Show', false);

        $this->actingAs($admin)->get('/categories?v2=1')
            ->assertOk()
            ->assertSee('Categories\\/Index', false);

        $this->actingAs($admin)->get("/categories/{$category->id}?v2=1")
            ->assertOk()
            ->assertSee('Categories\\/Show', false);
    }
}
