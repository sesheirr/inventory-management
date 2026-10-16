<?php

namespace Tests\Feature;

use App\Exports\BarangExport;
use App\Http\Controllers\AuthController;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\Room;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Notifications\MutationPending;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

        $export = new BarangExport;
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

    public function test_semi_realtime_live_state_api_returns_accurate_metrics_and_version_hash(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cat = Category::create(['name' => 'Jaringan']);
        $room = Room::create(['name' => 'Ruang NOC']);

        Product::create([
            'name' => 'Server Blade NOC',
            'kode_barang' => 'DKI-SRV-01',
            'barcode' => 'BRG-50001',
            'category_id' => $cat->id,
            'room_id' => $room->id,
            'stock' => 3,
            'status' => 'active',
        ]);

        Product::create([
            'name' => 'Kabel Patch Habis',
            'kode_barang' => 'DKI-KBL-02',
            'barcode' => 'BRG-50002',
            'category_id' => $cat->id,
            'room_id' => $room->id,
            'stock' => 0,
            'status' => 'out_of_stock',
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('live.state'))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('summary.total', 2)
            ->assertJsonPath('summary.active', 1)
            ->assertJsonPath('summary.low_stock', 1)
            ->assertJsonPath('summary.out_of_stock', 1)
            ->assertJsonPath('dashboard.total_products', 2)
            ->assertJsonPath('dashboard.total_categories', 1)
            ->assertJsonPath('dashboard.total_rooms', 1);

        $this->assertNotEmpty($response->json('version'));
    }

    public function test_short_deep_link_share_resolves_product_room_category_with_opengraph_metadata_and_redirect(): void
    {
        $cat = Category::create(['name' => 'Multimedia & Studio', 'description' => 'Peralatan liputan']);
        $room = Room::create(['name' => 'Ruang Media Center', 'person_in_charge' => 'Dedi']);
        $product = Product::create([
            'name' => 'Drone DJI Mavic 3 Enterprise',
            'kode_barang' => 'DKI-DRN-01',
            'barcode' => 'BRG-88001',
            'category_id' => $cat->id,
            'category' => $cat->name,
            'room_id' => $room->id,
            'stock' => 2,
            'status' => 'active',
            'description' => 'Drone dokumentasi udara Diskominfo Garut',
        ]);

        // Product short link renders OG meta tags & target URL
        $this->get(route('share.short', ['code' => 'p-'.$product->id]))
            ->assertOk()
            ->assertSee('Drone DJI Mavic 3 Enterprise', false)
            ->assertSee('og:title', false)
            ->assertSee('og:description', false)
            ->assertSee('og:image', false)
            ->assertSee(route('products.show', $product->id), false);

        // Direct redirect param ?redirect=1
        $this->get(route('share.short', ['code' => 'p-'.$product->id, 'redirect' => 1]))
            ->assertRedirect(route('products.show', $product->id));

        // Room short link
        $this->get(route('share.short', ['code' => 'r-'.$room->id]))
            ->assertOk()
            ->assertSee('Ruangan Ruang Media Center', false)
            ->assertSee(route('rooms.show', $room->id), false);

        // Category short link
        $this->get(route('share.short', ['code' => 'c-'.$cat->id]))
            ->assertOk()
            ->assertSee('Kategori Multimedia & Studio')
            ->assertSee(route('categories.show', $cat->id), false);
    }

    public function test_notification_system_handles_mutation_decision_low_stock_alert_and_seamless_tap_deep_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $operator = User::factory()->create(['role' => 'user']);
        $room = Room::create(['name' => 'Ruang TIK']);

        $product = Product::create([
            'name' => 'Modem Orbit Backup',
            'kode_barang' => 'DKI-MDM-01',
            'barcode' => 'BRG-33001',
            'room_id' => $room->id,
            'stock' => 4,
            'status' => 'active',
        ]);

        // Operator submits a mutation request
        $this->actingAs($operator)->post(route('mutations.store'), [
            'product_id' => $product->id,
            'type' => 'keluar',
            'quantity' => 2,
            'mutation_date' => now()->toDateString(),
            'note' => 'Dipakai posko bencana',
        ])->assertRedirect(route('mutations.index'));

        // Admin received database notification for pending mutation
        $adminNotif = $admin->unreadNotifications()->first();
        $this->assertNotNull($adminNotif);
        $this->assertSame('approval', $adminNotif->data['category'] ?? null);

        // Admin taps notification -> go() marks it read and redirects to pending mutations
        $this->actingAs($admin)
            ->get(route('notifications.go', $adminNotif->id))
            ->assertRedirect($adminNotif->data['url']);

        $this->assertNotNull($adminNotif->fresh()->read_at);

        // Admin approves the mutation -> Operator receives MutationStatusUpdated notification
        $mutation = Mutation::firstOrFail();
        $this->actingAs($admin)
            ->patch(route('mutations.approve', $mutation))
            ->assertRedirect(route('mutations.index'));

        $operatorNotif = $operator->unreadNotifications()->first();
        $this->assertNotNull($operatorNotif);
        $this->assertSame('approved', $operatorNotif->data['category'] ?? null);

        // Operator taps notification -> seamlessly redirected to the product detail page
        $this->actingAs($operator)
            ->get(route('notifications.go', $operatorNotif->id))
            ->assertRedirect(route('products.show', $product->id));

        $this->assertNotNull($operatorNotif->fresh()->read_at);

        // Reminder sync endpoint works without duplicating unread alerts
        $this->actingAs($admin)
            ->post(route('notifications.checkReminders'))
            ->assertRedirect();
    }

    public function test_superadmin_can_create_edit_and_reset_password_of_users_in_real_world_flow(): void
    {
        $superAdmin = User::factory()->create(['role' => 'superadmin']);

        // Create a new user
        $this->actingAs($superAdmin)
            ->post(route('users.store'), [
                'name' => 'Rizky Pratama, S.T.',
                'email' => 'rizky.pratama@garutkab.go.id',
                'role' => 'admin',
                'password' => 'Diskominfo2026!',
                'password_confirmation' => 'Diskominfo2026!',
            ])
            ->assertRedirect(route('users.index'));

        $createdUser = User::where('email', 'rizky.pratama@garutkab.go.id')->firstOrFail();
        $this->assertSame('admin', $createdUser->role);

        // Update user details & reset password
        $this->actingAs($superAdmin)
            ->put(route('users.update', $createdUser), [
                'name' => 'Rizky Pratama, S.T., M.Kom.',
                'email' => 'rizky.pratama@garutkab.go.id',
                'role' => 'user',
                'password' => 'NewPassword2026!',
                'password_confirmation' => 'NewPassword2026!',
            ])
            ->assertRedirect(route('users.index'));

        $createdUser->refresh();
        $this->assertSame('Rizky Pratama, S.T., M.Kom.', $createdUser->name);
        $this->assertSame('user', $createdUser->role);
        $this->assertTrue(Hash::check('NewPassword2026!', $createdUser->password));
    }

    public function test_pdf_export_routes_for_products_and_mutations_render_cleanly(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $room = Room::create(['name' => 'Ruang Command Center']);
        Product::create([
            'name' => 'Smart TV Wall 75 Inch',
            'kode_barang' => 'DKI-TV-75',
            'barcode' => 'BRG-75001',
            'room_id' => $room->id,
            'stock' => 2,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('products.export.pdf'))
            ->assertOk()
            ->assertSee('Dinas Komunikasi dan Informatika')
            ->assertSee('Smart TV Wall 75 Inch')
            ->assertSee('BRG-75001');

        $this->actingAs($user)
            ->get(route('mutations.export.pdf'))
            ->assertOk()
            ->assertSee('Laporan Riwayat Mutasi');
    }

    public function test_mutation_show_and_create_with_product_id_work_cleanly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $roomA = Room::create(['name' => 'Ruang NOC Utama', 'person_in_charge' => 'Budi Santoso, S.Kom']);
        $roomB = Room::create(['name' => 'Ruang Server Cadangan', 'person_in_charge' => 'Siti Aminah, S.T.']);

        $product = Product::create([
            'name' => 'Switch Core Cisco Catalyst 9300',
            'kode_barang' => 'DKI-SW-9300',
            'barcode' => 'BRG-93001',
            'room_id' => $roomA->id,
            'stock' => 4,
            'status' => 'active',
        ]);

        // 1. Visit /mutations/create?product_id=... -> must render 200 OK without Undefined variable $items
        $this->actingAs($admin)
            ->get(route('mutations.create', ['product_id' => $product->id]))
            ->assertOk()
            ->assertSee('Switch Core Cisco Catalyst 9300')
            ->assertSee('BRG-93001');

        // 2. Submit mutation with redirect_to=product (Quick Mutation Modal from Product Detail)
        $this->actingAs($admin)
            ->post(route('mutations.store'), [
                'product_id' => $product->id,
                'type' => 'pindah_ruang',
                'from_room_id' => $roomA->id,
                'to_room_id' => $roomB->id,
                'quantity' => 1,
                'mutation_date' => now()->toDateString(),
                'note' => 'Relokasi rak jaringan',
                'redirect_to' => 'product',
            ])
            ->assertRedirect(route('products.show', $product->id));

        $mutation = Mutation::where('product_id', $product->id)->firstOrFail();

        // 3. Visit /mutations/{mutation} -> MutationController::show() must render 200 OK
        $this->actingAs($admin)
            ->get(route('mutations.show', $mutation))
            ->assertOk()
            ->assertSee('Detail Transaksi Mutasi Barang')
            ->assertSee('Switch Core Cisco Catalyst 9300')
            ->assertSee('Ruang NOC Utama')
            ->assertSee('Ruang Server Cadangan')
            ->assertSee('Relokasi rak jaringan');

        // 4. Filter /mutations?product_id=...
        $this->actingAs($admin)
            ->get(route('mutations.index', ['product_id' => $product->id]))
            ->assertOk()
            ->assertSee('Switch Core Cisco Catalyst 9300');
    }

    public function test_room_person_in_charge_dropdown_manual_uniqueness_and_filter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Room::create([
            'name' => 'Ruang Studio Multimedia',
            'person_in_charge' => 'Andi Wijaya, S.Kom',
        ]);

        Room::create([
            'name' => 'Ruang Persandian',
            'person_in_charge' => 'Dewi Sartika, M.T.',
        ]);

        // 1. Selecting an existing PIC from dropdown (pic_mode=select) is allowed
        $this->actingAs($admin)
            ->post(route('rooms.store'), [
                'name' => 'Ruang Editing Video',
                'pic_mode' => 'select',
                'person_in_charge' => 'Andi Wijaya, S.Kom',
            ])
            ->assertRedirect(route('rooms.index'));

        // 2. Adding a manual PIC (pic_mode=manual) with an identical existing name (case-insensitive) is rejected
        $this->actingAs($admin)
            ->from(route('rooms.create'))
            ->post(route('rooms.store'), [
                'name' => 'Ruang Podcast',
                'pic_mode' => 'manual',
                'person_in_charge_manual' => 'andi wijaya, s.kom',
            ])
            ->assertRedirect(route('rooms.create'))
            ->assertSessionHasErrors('person_in_charge');

        // 3. Adding a manual PIC with a brand-new unique name succeeds
        $this->actingAs($admin)
            ->post(route('rooms.store'), [
                'name' => 'Ruang Podcast',
                'pic_mode' => 'manual',
                'person_in_charge_manual' => 'Fajar Nugraha, S.I.Kom',
            ])
            ->assertRedirect(route('rooms.index'));

        $this->assertDatabaseHas('rooms', [
            'name' => 'Ruang Podcast',
            'person_in_charge' => 'Fajar Nugraha, S.I.Kom',
        ]);

        // 4. Duplicate room name (case-insensitive) is rejected
        $this->actingAs($admin)
            ->from(route('rooms.create'))
            ->post(route('rooms.store'), [
                'name' => 'ruang podcast',
                'pic_mode' => 'select',
                'person_in_charge' => 'Fajar Nugraha, S.I.Kom',
            ])
            ->assertRedirect(route('rooms.create'))
            ->assertSessionHasErrors('name');

        // 5. Filtering /rooms?pic=... returns only rooms managed by that Penanggung Jawab
        $this->actingAs($admin)
            ->get(route('rooms.index', ['pic' => 'Andi Wijaya, S.Kom']))
            ->assertOk()
            ->assertSee('Ruang Studio Multimedia')
            ->assertSee('Ruang Editing Video')
            ->assertDontSee('Ruang Persandian');
    }

    public function test_mobile_native_ux_hero_card_app_launcher_and_notification_sheet(): void
    {
        $superadmin = User::factory()->create([
            'name' => 'Fauzan Super Admin',
            'role' => 'superadmin',
        ]);

        $response = $this->actingAs($superadmin)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Halo, Fauzan Super Admin!')
            ->assertSee('data-framer-page', false)
            ->assertSee('data-framer-card', false)
            ->assertSee('data-framer-stagger', false)
            ->assertSee('id="mobileAppMenuSheet"', false)
            ->assertSee('id="mobileBottomMenuBtn"', false)
            ->assertSee('id="notificationDropdownPanel"', false)
            ->assertSee('id="closeNotificationSheetBtn"', false)
            ->assertSee('hidden lg:flex lg:flex-col lg:w-64', false);
    }

    public function test_cloudflare_https_tunnel_headers_force_https_urls_and_support_remote_crud(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tunnelHeaders = [
            'Host' => 'diskominfo-garut.trycloudflare.com',
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Host' => 'diskominfo-garut.trycloudflare.com',
            'X-Forwarded-Port' => '443',
            'CF-Visitor' => '{"scheme":"https"}',
        ];

        $dashboardResponse = $this->actingAs($admin)
            ->withHeaders($tunnelHeaders)
            ->get('https://diskominfo-garut.trycloudflare.com/dashboard');

        $dashboardResponse->assertOk()
            ->assertSee('https://diskominfo-garut.trycloudflare.com/products', false);

        // Remote CRUD over Cloudflare HTTPS Tunnel
        $createResponse = $this->actingAs($admin)
            ->withHeaders($tunnelHeaders)
            ->post('https://diskominfo-garut.trycloudflare.com/categories', [
                'name' => 'Perangkat Tunnel HTTPS',
                'description' => 'Diakses via Cloudflare Free Tunnel',
            ]);

        $createResponse->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'Perangkat Tunnel HTTPS',
        ]);
    }

    public function test_login_remember_me_creates_7_day_quick_profile_and_supports_one_click_login_and_forget(): void
    {
        $user = User::factory()->create([
            'name' => 'Fauzan Super Admin',
            'email' => 'fauzan@garutkab.go.id',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
        ]);

        // 1. Login with remember_me checked -> sets inv_quick_profile cookie
        $loginRes = $this->post('/login', [
            'email' => 'fauzan@garutkab.go.id',
            'password' => 'password123',
            'remember_me' => '1',
        ]);

        $loginRes->assertRedirect('/dashboard');
        $loginRes->assertCookie(AuthController::QUICK_PROFILE_COOKIE);

        $cookiePayload = Crypt::encryptString((string) json_encode([
            'user_id' => $user->id,
            'email' => $user->email,
            'saved_at' => now()->timestamp,
            'expires_at' => now()->addDays(7)->timestamp,
            'password_hash_prefix' => substr((string) $user->password, 0, 16),
        ]));

        // 2. Logout and verify /login shows 1-Click Quick Sign-In card
        $this->post(route('logout'));
        $this->assertGuest();

        $loginPage = $this->withCookie(AuthController::QUICK_PROFILE_COOKIE, $cookiePayload)
            ->get(route('login'));

        $loginPage->assertOk()
            ->assertSee('Masuk Cepat')
            ->assertSee('Fauzan Super Admin')
            ->assertSee('fauzan@garutkab.go.id')
            ->assertSee('Super Admin')
            ->assertSee('id="quickLoginSubmitBtn"', false);

        // 3. Submit 1-Click Quick Sign-In -> logs user in without typing password
        $quickRes = $this->withCookie(AuthController::QUICK_PROFILE_COOKIE, $cookiePayload)
            ->post(route('login.quick'));

        $quickRes->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        // 4. Logout & clear saved profile
        $this->post(route('logout'));
        $forgetRes = $this->withCookie(AuthController::QUICK_PROFILE_COOKIE, $cookiePayload)
            ->post(route('login.forget-profile'));

        $forgetRes->assertRedirect(route('login'));
    }

    public function test_profile_smart_phone_detection_and_cropped_square_avatar_upload(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'name' => 'Fauzan',
            'email' => 'fauzan@garutkab.go.id',
            'role' => 'superadmin',
        ]);

        // 1. Check profile page has "Ubah", "Simpan", and role badge below name
        $this->actingAs($user)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Ubah')
            ->assertSee('Simpan')
            ->assertSee('Super Admin')
            ->assertSee('id="avatarCropCanvas"', false)
            ->assertSee('id="smartPhoneInput"', false);

        // 2. Save Indonesian local phone format (0812-3456-7890) + 1:1 cropped base64 avatar
        $tinyPngBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        $this->actingAs($user)
            ->post(route('profile.update'), [
                'name' => 'Fauzan Updated',
                'email' => 'fauzan@garutkab.go.id',
                'phone' => '0812-3456-7890',
                'phone_country_code' => '62',
                'cropped_avatar' => $tinyPngBase64,
            ])
            ->assertRedirect(route('profile'))
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('+6281234567890', $user->phone);
        $this->assertSame('+62 812-3456-7890', $user->formatted_phone);
        $this->assertSame('Telkomsel', $user->phone_meta['operator']);
        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);

        // 3. Save International phone format (+65 9123 4567 Singapore)
        $this->actingAs($user)
            ->post(route('profile.update'), [
                'name' => 'Fauzan Updated',
                'email' => 'fauzan@garutkab.go.id',
                'phone' => '+65 9123-4567',
                'phone_country_code' => '65',
            ])
            ->assertRedirect(route('profile'));

        $user->refresh();
        $this->assertSame('+6591234567', $user->phone);
        $this->assertSame('SG', $user->phone_meta['iso']);
    }

    public function test_notification_cross_origin_url_stays_relative_and_supports_json_swipe_actions(): void
    {
        $fauzan = User::factory()->create([
            'name' => 'Fauzan',
            'role' => 'superadmin',
        ]);

        // Insert a notification that has a legacy hardcoded localhost:8000 URL
        $notifId = (string) Str::uuid();
        DB::table('notifications')->insert([
            'id' => $notifId,
            'type' => LowStockAlert::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $fauzan->id,
            'data' => json_encode([
                'title' => 'Stok Menipis: Router NOC',
                'message' => 'Stok tersisa 2 unit.',
                'url' => 'http://localhost:8000/products?search=BRG-000101',
                'icon' => 'bi-exclamation-triangle-fill',
                'color' => 'amber',
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Clicking notification object routes to relative path on current host, never /login
        $goRes = $this->actingAs($fauzan)
            ->get(route('notifications.go', $notifId));

        $goRes->assertRedirect('/products?search=BRG-000101');
        $this->assertAuthenticatedAs($fauzan);

        // 2. Mobile swipe-left delete via JSON returns { ok: true }
        $delRes = $this->actingAs($fauzan)
            ->deleteJson(route('notifications.destroy', $notifId));

        $delRes->assertOk()
            ->assertJsonPath('ok', true);
    }

    public function test_products_list_grid_switcher_and_dashboard_semi_gradient_chart_render(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('data-product-view-btn="list"', false)
            ->assertSee('data-product-view-btn="grid"', false)
            ->assertSee('id="productsListView"', false)
            ->assertSee('id="productsGridView"', false);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Analitik Pergerakan & Distribusi Aset', false)
            ->assertSee('Kesehatan Stok & Mutasi', false)
            ->assertSee('id="dashPrimaryAreaGrad"', false);
    }

    public function test_landing_page_and_top_right_collapsible_theme_switcher_render_properly(): void
    {
        $landing = $this->get('/');
        $landing->assertOk()
            ->assertSee('SIM-INVENTARIS', false)
            ->assertSee('Diskominfo Kabupaten Garut', false)
            ->assertSee('id="landingThemeSwitcherRoot"', false)
            ->assertHeader('Server-Timing');

        $login = $this->get(route('login'));
        $login->assertOk()
            ->assertSee('id="authThemeSwitcherRoot"', false)
            ->assertSee('id="authThemeCollapsibleBtn"', false)
            ->assertSee('id="authThemeCollapsibleMenu"', false)
            ->assertSee('id="secChallengeBox"', false)
            ->assertSee('id="secChallengeRender"', false)
            ->assertSee('id="secChallengeInput"', false)
            ->assertSee('name="security_code"', false)
            ->assertSee('<svg ', false)
            ->assertDontSee('id="captcha-img"', false)
            ->assertDontSee('id="reload-captcha"', false);

        // Verify adblock-immune token refresh route returns fresh inline SVG and updates session code
        $syncRes = $this->getJson(route('auth.token.sync'));
        $syncRes->assertOk()
            ->assertJsonStructure(['svg']);
        $this->assertStringContainsString('<svg', (string) $syncRes->json('svg'));
        $this->assertNotEmpty(session('captcha_code'));
    }

    public function test_observability_dashboard_server_timing_header_and_official_telescope_gate(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $regularUser = User::factory()->create(['role' => 'user']);

        // Regular user cannot access /observability or viewTelescope gate
        $this->actingAs($regularUser)
            ->get(route('observability.index'))
            ->assertForbidden();

        $this->assertFalse(Gate::forUser($regularUser)->allows('viewTelescope'));
        $this->assertTrue(Gate::forUser($admin)->allows('viewTelescope'));

        // Admin can access /observability and see Server-Timing + Telescope link
        $res = $this->actingAs($admin)->get(route('observability.index'));
        $res->assertOk()
            ->assertHeader('Server-Timing')
            ->assertHeader('X-Observability-Duration-Ms')
            ->assertSee('Observability &amp; Diagnostik Performa Sistem', false)
            ->assertSee('Buka Laravel Telescope', false);

        // Admin can clear observability trace buffer
        $this->actingAs($admin)
            ->post(route('observability.clear'))
            ->assertRedirect(route('observability.index'));
    }

    public function test_custom_animated_maintenance_mode_503_preview_renders_properly(): void
    {
        $res = $this->get(route('maintenance.preview'));
        $res->assertStatus(503)
            ->assertSee('HTTP 503 · MAINTENANCE MODE', false)
            ->assertSee('Sistem Sedang Menjalankan Pembaruan Terjadwal', false)
            ->assertSee('ERR_MAINTENANCE_503', false);
    }

    public function test_smart_database_sync_command_handles_sparse_and_irrelevant_data_strategies(): void
    {
        $customUser = User::factory()->create([
            'name' => 'Pegawai Lokal Custom',
            'email' => 'pegawai.lokal@garutkab.go.id',
            'role' => 'user',
        ]);

        $cat = Category::create(['name' => 'Kategori Sementara']);
        $room = Room::create(['name' => 'Ruang Sementara']);

        // Insert irrelevant English placeholder item
        Product::create([
            'name' => 'Product 1',
            'kode_barang' => 'OLD-001',
            'barcode' => 'OLD-BRG-001',
            'category_id' => $cat->id,
            'category' => $cat->name,
            'room_id' => $room->id,
            'stock' => 5,
            'price' => 10000,
            'status' => 'active',
            'description' => 'Premium inventory item crafted for modern store operations.',
        ]);

        // Running smart-db-sync should detect irrelevant data, keep user identity, and replace inventory with Diskominfo seeds
        $this->artisan('inventory:smart-db-sync')
            ->assertExitCode(0);

        // User identity is preserved!
        $this->assertDatabaseHas('users', [
            'email' => 'pegawai.lokal@garutkab.go.id',
            'name' => 'Pegawai Lokal Custom',
        ]);

        // Irrelevant product is replaced with official Diskominfo catalog
        $this->assertDatabaseMissing('products', [
            'name' => 'Product 1',
        ]);
        $this->assertDatabaseHas('products', [
            'kode_barang' => 'DKI-GRT-2026-001',
        ]);
        $this->assertGreaterThanOrEqual(15, Product::count());
    }
}
