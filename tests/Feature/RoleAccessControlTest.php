<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => 'superadmin',
            'email' => 'superadmin@example.com',
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com',
        ]);

        $this->regularUser = User::factory()->create([
            'role' => 'user',
            'email' => 'user@example.com',
        ]);
    }

    public function test_admin_can_access_operational_menus_and_actions(): void
    {
        $room = Room::create(['name' => 'Gudang Utama']);
        $category = Category::create(['name' => 'Elektronik']);

        // Admin can access Room index & create
        $this->actingAs($this->admin)->get(route('rooms.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('rooms.create'))->assertOk();
        $this->actingAs($this->admin)->post(route('rooms.store'), [
            'name' => 'Ruang Baru Admin',
        ])->assertRedirect(route('rooms.index'));

        // Admin can access Category index & store
        $this->actingAs($this->admin)->get(route('categories.index'))->assertOk();
        $this->actingAs($this->admin)->post(route('categories.store'), [
            'name' => 'Kategori Baru Admin',
        ])->assertRedirect(route('categories.index'));

        // Admin can access Product index & create
        $this->actingAs($this->admin)->get(route('products.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('products.create'))->assertOk();

        // Admin can access Mutations and approve/reject
        $product = Product::create([
            'name' => 'Laptop ROG',
            'kode_barang' => 'BRG-123456',
            'category_id' => $category->id,
            'category' => 'Elektronik',
            'room_id' => $room->id,
            'stock' => 10,
            'status' => 'active',
        ]);

        $mutation = Mutation::create([
            'product_id' => $product->id,
            'user_id' => $this->regularUser->id,
            'type' => 'masuk',
            'quantity' => 5,
            'mutation_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)->get(route('mutations.index'))->assertOk();
        $this->actingAs($this->admin)->patch(route('mutations.approve', $mutation))
            ->assertRedirect();

        $this->assertEquals('approved', $mutation->fresh()->status);
    }

    public function test_admin_cannot_access_user_management_and_gets_403(): void
    {
        // Admin accessing /users URL directly is rejected with 403
        $this->actingAs($this->admin)->get(route('users.index'))
            ->assertForbidden();

        // Admin attempting to update role via direct request is rejected with 403
        $this->actingAs($this->admin)
            ->put(route('users.update-role', $this->regularUser), [
                'role' => 'admin',
            ])
            ->assertForbidden();

        // Admin cannot change own role
        $this->actingAs($this->admin)
            ->put(route('users.update-role', $this->admin), [
                'role' => 'superadmin',
            ])
            ->assertForbidden();

        // Admin cannot delete user
        $this->actingAs($this->admin)
            ->delete(route('users.destroy', $this->regularUser))
            ->assertForbidden();
    }

    public function test_superadmin_can_access_user_management_and_update_roles(): void
    {
        // Superadmin can view users list
        $this->actingAs($this->superAdmin)->get(route('users.index'))->assertOk();

        // Superadmin can update regular user role to admin
        $this->actingAs($this->superAdmin)
            ->put(route('users.update-role', $this->regularUser), [
                'role' => 'admin',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertEquals('admin', $this->regularUser->fresh()->role);
    }

    public function test_superadmin_cannot_change_own_role(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('users.update-role', $this->superAdmin), [
                'role' => 'admin',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error', 'Anda tidak bisa mengubah role akun Anda sendiri.');

        $this->assertEquals('superadmin', $this->superAdmin->fresh()->role);
    }

    public function test_cannot_downgrade_the_last_superadmin(): void
    {
        $anotherSuperAdmin = User::factory()->create([
            'role' => 'superadmin',
            'email' => 'superadmin2@example.com',
        ]);

        // When there are 2 superadmins, one superadmin can update another superadmin's role if needed
        $this->actingAs($this->superAdmin)
            ->put(route('users.update-role', $anotherSuperAdmin), [
                'role' => 'admin',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertEquals('admin', $anotherSuperAdmin->fresh()->role);

        // Now only 1 superadmin left, attempting to downgrade should be blocked
        $this->actingAs($this->superAdmin)
            ->put(route('users.update-role', $this->superAdmin), [
                'role' => 'admin',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error', 'Anda tidak bisa mengubah role akun Anda sendiri.');
    }

    public function test_regular_user_cannot_access_admin_or_superadmin_actions(): void
    {
        $this->actingAs($this->regularUser)->get(route('users.index'))->assertForbidden();
        $this->actingAs($this->regularUser)->get(route('rooms.create'))->assertForbidden();
        $this->actingAs($this->regularUser)->post(route('categories.store'), ['name' => 'Test'])->assertForbidden();
    }
}
