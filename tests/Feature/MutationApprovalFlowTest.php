<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Mutation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MutationApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_auto_approves_mutation_and_updates_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create([
            'name' => 'Laptop',
            'category' => 'Elektronik',
            'stock' => 10,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('mutations.store'), [
                'product_id' => $product->id,
                'type' => 'keluar',
                'quantity' => 3,
                'mutation_date' => now()->toDateString(),
                'note' => 'Pemakaian internal',
            ])
            ->assertRedirect(route('mutations.index'));

        $this->assertDatabaseHas('mutations', [
            'product_id' => $product->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);

        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_user_mutation_stays_pending_and_does_not_reduce_stock(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $product = Product::create([
            'name' => 'Printer',
            'category' => 'Office',
            'stock' => 10,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('mutations.store'), [
                'product_id' => $product->id,
                'type' => 'keluar',
                'quantity' => 2,
                'mutation_date' => now()->toDateString(),
                'note' => 'Pengajuan user',
            ])
            ->assertRedirect(route('mutations.index'));

        $this->assertDatabaseHas('mutations', [
            'product_id' => $product->id,
            'status' => 'pending',
            'approved_by' => null,
        ]);

        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_admin_can_approve_pending_mutation_and_update_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $product = Product::create([
            'name' => 'Monitor',
            'category' => 'Elektronik',
            'stock' => 15,
            'status' => 'active',
        ]);

        $mutation = Mutation::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'type' => 'keluar',
            'quantity' => 4,
            'mutation_date' => now()->toDateString(),
            'note' => 'Butuh approval',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('mutations.approve', $mutation))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('mutations', [
            'id' => $mutation->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);

        $this->assertSame(11, $product->fresh()->stock);
    }
}
