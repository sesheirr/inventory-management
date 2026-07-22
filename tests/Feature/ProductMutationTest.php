<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductMutationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_record_stock_incoming_and_update_product_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Laptop',
            'category' => 'Elektronik',
            'stock' => 5,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('products.mutations.store', $product), [
            'movement_type' => 'incoming',
            'quantity' => 3,
            'note' => 'Restock dari supplier',
            'mutation_date' => '2026-07-22',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $product->refresh();
        $this->assertSame(8, $product->stock);
        $this->assertDatabaseHas('mutations', [
            'product_id' => $product->id,
            'movement_type' => 'incoming',
            'quantity' => 3,
            'note' => 'Restock dari supplier',
        ]);
    }
}
