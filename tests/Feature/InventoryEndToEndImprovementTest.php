<?php

namespace Tests\Feature;

use App\Exports\BarangExport;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Room;
use App\Models\User;
use App\Notifications\MutationPending;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InventoryEndToEndImprovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_created_product_is_immediately_visible_to_other_users_across_all_roles(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $superAdmin = User::factory()->create(['role' => 'superadmin']);
        $otherOperator = User::factory()->create(['role' => 'user']);

        $category = Category::create([
            'name' => 'Perangkat Jaringan & Server',
            'description' => 'Infrastruktur NOC',
        ]);
        $room = Room::create([
            'name' => 'Ruang Server & NOC',
            'person_in_charge' => 'Hendra Wijaya',
        ]);

        // Admin creates a new product with an image
        $response = $this->actingAs($admin)->post(route('products.store'), [
            'name' => 'Router MikroTik CCR2116 Core NOC',
            'kode_barang' => 'DKI-TEST-001',
            'barcode' => 'brg-90001',
            'category_id' => $category->id,
            'room_id' => $room->id,
            'subcategory' => 'Core Router',
            'edition' => '2026',
            'stock' => 4,
            'price' => 21500000,
            'status' => 'active',
            'description' => 'Router utama jaringan Pemkab Garut',
            'image' => UploadedFile::fake()->image('router.jpg', 600, 400),
        ]);

        $response->assertRedirect(route('products.index'));

        $product = Product::where('kode_barang', 'DKI-TEST-001')->firstOrFail();
        $this->assertSame('BRG-90001', $product->barcode);
        $this->assertSame($category->id, $product->category_id);
        $this->assertSame($room->id, $product->room_id);
        $this->assertNotNull($product->image_url);

        // Other operator (regular user) immediately sees the product on index, show, dashboard, category, and room pages
        $this->actingAs($otherOperator)
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('Router MikroTik CCR2116 Core NOC')
            ->assertSee('Ruang Server & NOC');

        $this->actingAs($otherOperator)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Router MikroTik CCR2116 Core NOC')
            ->assertSee('BRG-90001')
            ->assertSee('DKI-TEST-001');

        $this->actingAs($otherOperator)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Router MikroTik CCR2116 Core NOC');

        // SuperAdmin also sees the product in catalog and sidebar navigation
        $this->actingAs($superAdmin)
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('Router MikroTik CCR2116 Core NOC')
            ->assertSee('Manajemen User');

        // Simulate another developer machine where the local storage file does not exist on disk:
        // image_url must gracefully fall back to the embedded data_url in media.custom_properties
        $media = $product->getFirstMedia('images');
        $this->assertNotNull($media);
        $this->assertNotEmpty($media->getCustomProperty('data_url'));
        Storage::disk('public')->deleteDirectory((string) $media->id);
        if ($legacyPath = $media->getCustomProperty('legacy_path')) {
            Storage::disk('public')->delete($legacyPath);
        }

        $fallbackUrl = $product->fresh()->image_url;
        $this->assertNotNull($fallbackUrl);
        $this->assertStringStartsWith('data:image/', $fallbackUrl);
    }

    public function test_product_update_properly_updates_room_category_price_and_barcode(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cat1 = Category::create(['name' => 'Kategori Lama']);
        $cat2 = Category::create(['name' => 'Kategori Baru']);
        $room1 = Room::create(['name' => 'Ruang Lama']);
        $room2 = Room::create(['name' => 'Ruang Baru GCC']);

        $product = Product::create([
            'name' => 'Switch Cisco 24 Port',
            'kode_barang' => 'DKI-SW-01',
            'barcode' => 'BRG-10001',
            'category_id' => $cat1->id,
            'category' => $cat1->name,
            'room_id' => $room1->id,
            'stock' => 5,
            'price' => 10000000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->put(route('products.update', $product), [
            'name' => 'Switch Cisco Catalyst 24 Port PoE+',
            'kode_barang' => 'DKI-SW-01-REV',
            'barcode' => ' brg-10002 ',
            'category_id' => $cat2->id,
            'room_id' => $room2->id,
            'stock' => 0,
            'price' => 15500000,
            'status' => 'out_of_stock',
            'description' => 'Sudah dipindahkan ke Ruang Baru GCC',
        ]);

        $response->assertRedirect(route('products.index'));

        $product->refresh();
        $this->assertSame('Switch Cisco Catalyst 24 Port PoE+', $product->name);
        $this->assertSame('DKI-SW-01-REV', $product->kode_barang);
        $this->assertSame('BRG-10002', $product->barcode);
        $this->assertSame($cat2->id, $product->category_id);
        $this->assertSame('Kategori Baru', $product->getRawOriginal('category'));
        $this->assertSame($room2->id, $product->room_id);
        $this->assertEquals(15500000, $product->price);
        $this->assertSame('out_of_stock', $product->status);
        $this->assertSame('Stok Habis', $product->status_label);
    }

    public function test_product_search_and_multi_filter_by_kode_barang_barcode_category_room_and_status(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $catNet = Category::create(['name' => 'Jaringan']);
        $catCam = Category::create(['name' => 'Multimedia']);
        $roomNoc = Room::create(['name' => 'Ruang NOC']);
        $roomIkp = Room::create(['name' => 'Ruang IKP']);

        Product::create([
            'name' => 'Firewall FortiGate 100F',
            'kode_barang' => 'DKI-FW-100',
            'barcode' => 'BRG-77701',
            'category_id' => $catNet->id,
            'category' => $catNet->name,
            'room_id' => $roomNoc->id,
            'stock' => 2,
            'status' => 'active',
        ]);

        Product::create([
            'name' => 'Kamera Sony Alpha A7 IV',
            'kode_barang' => 'DKI-CAM-200',
            'barcode' => 'BRG-77702',
            'category_id' => $catCam->id,
            'category' => $catCam->name,
            'room_id' => $roomIkp->id,
            'stock' => 12,
            'status' => 'active',
        ]);

        // Search by kode_barang
        $this->actingAs($user)
            ->get(route('products.index', ['search' => 'DKI-FW-100']))
            ->assertOk()
            ->assertSee('Firewall FortiGate 100F')
            ->assertDontSee('Kamera Sony Alpha A7 IV');

        // Search by barcode
        $this->actingAs($user)
            ->get(route('products.index', ['search' => 'BRG-77702']))
            ->assertOk()
            ->assertSee('Kamera Sony Alpha A7 IV')
            ->assertDontSee('Firewall FortiGate 100F');

        // Search by Room name
        $this->actingAs($user)
            ->get(route('products.index', ['search' => 'Ruang NOC']))
            ->assertOk()
            ->assertSee('Firewall FortiGate 100F')
            ->assertDontSee('Kamera Sony Alpha A7 IV');

        // Filter by low_stock (stock between 1 and 5)
        $this->actingAs($user)
            ->get(route('products.index', ['status' => 'low_stock']))
            ->assertOk()
            ->assertSee('Firewall FortiGate 100F')
            ->assertDontSee('Kamera Sony Alpha A7 IV');

        // Filter by room_id and sort
        $this->actingAs($user)
            ->get(route('products.index', ['room_id' => $roomIkp->id, 'sort' => 'stock_desc']))
            ->assertOk()
            ->assertSee('Kamera Sony Alpha A7 IV')
            ->assertDontSee('Firewall FortiGate 100F');
    }

    public function test_category_rename_synchronizes_legacy_category_column_on_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Alat Jaringan']);

        $product = Product::create([
            'name' => 'Access Point UniFi',
            'category_id' => $category->id,
            'category' => 'Alat Jaringan',
            'stock' => 5,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->put(route('categories.update', $category), [
                'name' => 'Infrastruktur Jaringan & Server',
                'description' => 'Kategori yang telah diperbarui',
            ])
            ->assertRedirect(route('categories.index'));

        $this->assertSame('Infrastruktur Jaringan & Server', $product->fresh()->getRawOriginal('category'));
    }

    public function test_barang_export_correctly_formats_string_statuses_and_downloads_xlsx(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Product::create([
            'name' => 'Laptop Uji Export',
            'kode_barang' => 'DKI-EXP-01',
            'barcode' => 'BRG-EXP01',
            'category' => 'Komputer',
            'stock' => 10,
            'status' => 'active',
        ]);

        $export = new BarangExport();
        $this->assertSame('Aktif', $export->formatStatus('active', 10));
        $this->assertSame('Stok Habis', $export->formatStatus('active', 0));
        $this->assertSame('Stok Habis', $export->formatStatus('out_of_stock', 0));
        $this->assertSame('Tidak Aktif', $export->formatStatus('inactive', 5));

        $this->actingAs($admin)
            ->get(route('products.export'))
            ->assertOk();
    }

    public function test_bulk_delete_products_triggers_activity_logs_and_removes_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $p1 = Product::create(['name' => 'Barang Hapus 1', 'category' => 'Umum', 'stock' => 1, 'status' => 'active']);
        $p2 = Product::create(['name' => 'Barang Hapus 2', 'category' => 'Umum', 'stock' => 2, 'status' => 'active']);

        $this->actingAs($admin)
            ->delete(route('products.destroySelected.delete'), [
                'selected_ids' => [$p1->id, $p2->id],
            ])
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', ['id' => $p1->id]);
        $this->assertDatabaseMissing('products', ['id' => $p2->id]);
        $this->assertGreaterThanOrEqual(2, ActivityLog::where('action', 'deleted')->count());
    }

    public function test_pending_mutation_by_regular_user_sends_notification_to_admins(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $superAdmin = User::factory()->create(['role' => 'superadmin']);
        $operator = User::factory()->create(['role' => 'user']);
        $room = Room::create(['name' => 'Ruang Studio']);
        $product = Product::create([
            'name' => 'Kamera Liputan',
            'category' => 'Multimedia',
            'stock' => 4,
            'status' => 'active',
        ]);

        $this->actingAs($operator)
            ->post(route('mutations.store'), [
                'product_id' => $product->id,
                'to_room_id' => $room->id,
                'type' => 'pindah_ruang',
                'quantity' => 1,
                'mutation_date' => now()->toDateString(),
                'note' => 'Pinjam untuk liputan',
            ])
            ->assertRedirect(route('mutations.index'));

        Notification::assertSentTo([$admin, $superAdmin], MutationPending::class);
    }

    public function test_barcode_scan_search_matches_both_barcode_and_kode_barang(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $product = Product::create([
            'name' => 'Scanner Fujitsu iX1600',
            'kode_barang' => 'DKI-SCN-099',
            'barcode' => 'BRG-000099',
            'category' => 'Kantor',
            'stock' => 3,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->postJson(route('products.scan.search'), ['barcode' => 'BRG-000099'])
            ->assertOk()
            ->assertJson([
                'found' => true,
                'redirect_url' => route('products.show', $product->id),
            ]);

        $this->actingAs($user)
            ->postJson(route('products.scan.search'), ['barcode' => 'DKI-SCN-099'])
            ->assertOk()
            ->assertJson([
                'found' => true,
                'redirect_url' => route('products.show', $product->id),
            ]);
    }

    public function test_database_seeder_preserves_all_existing_credentials_and_seeds_rich_diskominfo_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'admin@web.inventaris', 'role' => 'superadmin']);
        $this->assertDatabaseHas('users', ['email' => 'game.fauzann@gmail.com', 'role' => 'superadmin']);
        $this->assertDatabaseHas('users', ['email' => 'admin@inventory.local', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com', 'role' => 'user']);

        $this->assertEquals(8, Room::count());
        $this->assertEquals(6, Category::count());
        $this->assertEquals(25, Product::count());
        $this->assertEquals(0, Product::whereNull('category_id')->count());
        $this->assertEquals(0, Product::whereNull('room_id')->count());
    }
}
