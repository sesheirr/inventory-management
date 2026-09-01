<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryAccessAndValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_and_create_category(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/categories');
        $response->assertOk();

        $createResponse = $this->actingAs($user)->post('/categories', [
            'name' => 'Peralatan Kantor',
        ]);

        $createResponse->assertRedirect('/categories');
        $this->assertDatabaseHas('categories', ['name' => 'Peralatan Kantor']);
    }

    public function test_user_category_validation_rejects_empty_and_duplicate_names(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->from('/categories')->post('/categories', ['name' => ''])
            ->assertSessionHasErrors(['name']);

        Category::create(['name' => 'Elektronik']);

        $this->actingAs($user)->from('/categories')->post('/categories', ['name' => 'Elektronik'])
            ->assertSessionHasErrors(['name']);
    }

    public function test_new_category_appears_in_product_dropdown(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->post('/categories', ['name' => 'Kendaraan']);

        $response = $this->actingAs($user)->get('/products/create');

        $response->assertOk();
        $response->assertSee('Kendaraan');
    }

    public function test_category_delete_is_blocked_when_in_use_by_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Mebel']);
        $room = Room::create(['name' => 'Gudang']);

        Product::create([
            'name' => 'Meja',
            'category_id' => $category->id,
            'category' => $category->name,
            'room_id' => $room->id,
            'stock' => 1,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->from('/categories')->delete('/categories/' . $category->id);

        $response->assertRedirect('/categories');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
