<?php

/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Mutations, Profile, Notifications & Admin Observability Tests
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\Room;
use App\Models\User;
use App\Models\WebhookEndpoint;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class V2MutationsProfileAdminObservabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_v2_mutations_profile_notifications_and_admin_pages_render_and_enforce_rbac(): void
    {
        $requiredPages = [
            'resources/js/pages/Mutations/Index.tsx',
            'resources/js/pages/Mutations/Create.tsx',
            'resources/js/pages/Mutations/Show.tsx',
            'resources/js/pages/Profile/Index.tsx',
            'resources/js/pages/Settings/Index.tsx',
            'resources/js/pages/Notifications/Index.tsx',
            'resources/js/pages/Admin/Users/Index.tsx',
            'resources/js/pages/Admin/ActivityLogs/Index.tsx',
            'resources/js/pages/Admin/Observability/Index.tsx',
        ];

        foreach ($requiredPages as $pagePath) {
            $full = base_path($pagePath);
            $this->assertFileExists($full, "Missing v2 page: {$pagePath}");
            $this->assertStringContainsString('fwzann-abd', (string) file_get_contents($full));
        }

        $regularUser = User::factory()->create(['role' => User::ROLE_USER]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPERADMIN]);

        $category = Category::create(['name' => 'Perangkat Audio Visual']);
        $roomA = Room::create(['name' => 'Ruang Rapat Utama', 'code' => 'RR-01']);
        $roomB = Room::create(['name' => 'Command Center', 'code' => 'CC-01']);
        $product = Product::create([
            'name' => 'Smart Interactive Flat Panel 86"',
            'kode_barang' => 'BRG-IFP86',
            'barcode' => 'INV-IFP86',
            'category_id' => $category->id,
            'room_id' => $roomA->id,
            'stock' => 5,
            'status' => 'active',
        ]);

        $mutation = Mutation::create([
            'product_id' => $product->id,
            'user_id' => $regularUser->id,
            'type' => 'pindah_ruang',
            'from_room_id' => $roomA->id,
            'to_room_id' => $roomB->id,
            'quantity' => 1,
            'mutation_date' => now()->toDateString(),
            'status' => 'pending',
            'note' => 'Dipindahkan ke Command Center',
        ]);

        // 1. Regular user can access Mutations, Profile, Settings, Notifications in v2
        $this->actingAs($regularUser)->get('/mutations?v2=1')
            ->assertOk()
            ->assertSee('Mutations\\/Index', false);

        $this->actingAs($regularUser)->get('/mutations/create?v2=1')
            ->assertOk()
            ->assertSee('Mutations\\/Create', false);

        $this->actingAs($regularUser)->get("/mutations/{$mutation->id}?v2=1")
            ->assertOk()
            ->assertSee('Mutations\\/Show', false);

        $this->actingAs($regularUser)->get('/profile?v2=1')
            ->assertOk()
            ->assertSee('Profile\\/Index', false);

        $this->actingAs($regularUser)->get('/settings?v2=1')
            ->assertOk()
            ->assertSee('Settings\\/Index', false);

        $this->actingAs($regularUser)->get('/notifications?v2=1')
            ->assertOk()
            ->assertSee('Notifications\\/Index', false);

        // 2. Regular user MUST be forbidden from Observability, Telescope, Webhooks, Activity Logs, Users
        $this->actingAs($regularUser)->get('/observability?v2=1')->assertForbidden();
        $this->actingAs($regularUser)->get('/webhooks?v2=1')->assertForbidden();
        $this->actingAs($regularUser)->get('/activity-logs?v2=1')->assertForbidden();
        $this->actingAs($regularUser)->get('/users?v2=1')->assertForbidden();

        // 3. Admin can access Observability, Webhooks, Activity Logs in v2
        $this->actingAs($admin)->get('/observability?v2=1')
            ->assertOk()
            ->assertSee('Admin\\/Observability\\/Index', false);

        $this->actingAs($admin)->get('/activity-logs?v2=1')
            ->assertOk()
            ->assertSee('Admin\\/ActivityLogs\\/Index', false);

        // 4. SuperAdmin can access User Management in v2
        $this->actingAs($superAdmin)->get('/users?v2=1')
            ->assertOk()
            ->assertSee('Admin\\/Users\\/Index', false);

        // 5. Approving a pending mutation triggers outgoing HMAC-SHA256 webhook broadcast
        WebhookEndpoint::create([
            'name' => 'Audit Mutasi Garut',
            'target_url' => 'https://audit.garutkab.go.id/webhook',
            'secret' => 'mutasi-secret-key-2026',
            'events' => ['mutation.approved'],
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        Http::fake([
            'audit.garutkab.go.id/*' => Http::response(['ok' => true], 200),
        ]);

        $this->actingAs($admin)
            ->patch(route('mutations.approve', $mutation))
            ->assertRedirect();

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'audit.garutkab.go.id/webhook')
                && $request->header('X-Diskominfo-Event')[0] === 'mutation.approved';
        });

        // 6. Webhook event stream endpoint returns real-time HMAC-SHA256 events & notifications
        $streamRes = $this->actingAs($regularUser)->getJson('/api/webhooks/stream');
        $streamRes->assertOk()
            ->assertJsonPath('channel', 'webhook-hmac-sha256')
            ->assertJsonStructure([
                'ok',
                'channel',
                'timestamp',
                'cursor',
                'events',
                'notifications' => [
                    'unread_count',
                    'latest',
                ],
            ]);
        $events = $streamRes->json('events');
        $this->assertNotEmpty($events);
        $this->assertSame('mutation.approved', $events[0]['event'] ?? null);

        // 7. Notifications Blade view renders Red Dot indicator instead of blue "Baru" pill
        $notifHtml = $this->actingAs($regularUser)->get('/notifications')->assertOk()->getContent();
        $this->assertStringContainsString('data-notif-red-dot', (string) $notifHtml);
        $this->assertStringNotContainsString('bg-blue-100 text-blue-700', (string) $notifHtml);

        // 8. 1-Second Live Observability Metrics JSON endpoint enforces RBAC & returns 1000ms interval
        $this->actingAs($regularUser)->getJson('/observability/metrics')->assertForbidden();
        $this->actingAs($admin)->getJson('/observability/metrics')
            ->assertOk()
            ->assertJsonPath('refresh_interval_ms', 1000)
            ->assertJsonStructure([
                'ok',
                'refresh_interval_ms',
                'entries',
                'summary',
                'tableStats',
                'runtimeInfo',
                'max_entries',
            ]);

        // 9. Dashboard removes misleading "Total Nilai Aset Tercatat" and seeds 6-month historical mutations
        $this->seed(ProductSeeder::class);
        $dashBlade = $this->actingAs($admin)->get('/dashboard')->assertOk();
        $dashBlade->assertDontSee('Total Nilai Aset Tercatat');
        $dashBlade->assertSee('dashChartTooltip', false);
        $dashBlade->assertSee('shadcnLineChartJson', false);
        $this->assertGreaterThanOrEqual(16, Mutation::count());
    }

    public function test_v2_mobile_native_ux_warm_dark_telemetry_pwa_and_deploy_suite(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        // 1. Mobile notification popover anchored near bell + PWA install & permissions modal in layout
        $dashHtml = (string) $this->actingAs($admin)->get('/dashboard')->assertOk()->getContent();
        $this->assertStringContainsString('id="notificationDropdownPanel"', $dashHtml);
        $this->assertStringContainsString('id="mobilePwaInstallPromptModal"', $dashHtml);
        $this->assertStringContainsString('id="pwaOneClickInstallBtn"', $dashHtml);
        $this->assertStringContainsString('id="pwaManualInstallGuideBox"', $dashHtml);
        $this->assertStringContainsString('id="pwaRequestPermissionsBtn"', $dashHtml);
        $this->assertStringContainsString('id="pwaPermCameraBadge"', $dashHtml);
        $this->assertStringContainsString('id="pwaPermLocationBadge"', $dashHtml);
        $this->assertStringContainsString('id="pwaPermDeeplinkBadge"', $dashHtml);
        $this->assertStringContainsString('dark:bg-[#12100e]', $dashHtml);

        // 2. CSS Warm Dark Mode + Toast Fade In/Out + Uniform Page Enter/Exit + Pull-to-Refresh + Scanner HUD
        $appCss = (string) file_get_contents(base_path('resources/css/app.css'));
        $this->assertStringContainsString('#12100e', $appCss);
        $this->assertStringContainsString('toastFadeIn60', $appCss);
        $this->assertStringContainsString('toastFadeOut60', $appCss);
        $this->assertStringContainsString('pageEnterUniform60', $appCss);
        $this->assertStringContainsString('pageExitUniform60', $appCss);
        $this->assertStringContainsString('.ptr-capsule', $appCss);
        $this->assertStringContainsString('.scanner-laser-line', $appCss);

        // 3. Fullscreen Banking Barcode Scanner HUD + Reusable Media Uploader Thumbnail Grid
        $scanHtml = (string) $this->actingAs($admin)->get('/scan-barcode')->assertOk()->getContent();
        $this->assertStringContainsString('id="bankingScannerFullscreenContainer"', $scanHtml);

        $createProductHtml = (string) $this->actingAs($admin)->get('/products/create')->assertOk()->getContent();
        $this->assertStringContainsString('data-reusable-media-uploader', $createProductHtml);
        $this->assertStringContainsString('data-uploader-preview-grid', $createProductHtml);

        // 4. Dynamic Active Filter Cards on /products (and none active when reset)
        $productsUnfiltered = (string) $this->actingAs($admin)->get('/products')->assertOk()->getContent();
        $this->assertStringNotContainsString('data-card-active="true"', $productsUnfiltered);

        $productsFiltered = (string) $this->actingAs($admin)->get('/products?stock_level=low')->assertOk()->getContent();
        $this->assertStringContainsString('data-card-active="true"', $productsFiltered);

        // 5. Rich Activity Log tracking + filtering, sorting & pagination
        ActivityLog::record(
            action: 'scan_barcode',
            module: 'BARCODE',
            description: 'Memindai barcode inventaris BRG-TEST-01 via Fullscreen Banking HUD.',
            subjectLabel: 'BRG-TEST-01'
        );

        $logsRes = $this->actingAs($admin)
            ->get('/activity-logs?action=scan_barcode&sort=latest&per_page=10')
            ->assertOk();
        $logsRes->assertSee('BRG-TEST-01');

        // 6. Observability pagination + 1000ms heartbeat
        $obsJson = $this->actingAs($admin)
            ->getJson('/observability/metrics?page=1&per_page=10&method=GET')
            ->assertOk()
            ->assertJsonPath('refresh_interval_ms', 1000)
            ->assertJsonStructure([
                'ok',
                'refresh_interval_ms',
                'entries',
                'pagination' => ['current_page', 'per_page', 'total', 'last_page', 'from', 'to'],
            ]);
        $this->assertSame(10, $obsJson->json('pagination.per_page'));

        // 7. Live Realtime Notification JSON actions (no page reload required)
        $this->actingAs($admin)
            ->postJson('/notifications/check-reminders')
            ->assertOk()
            ->assertJsonStructure(['ok', 'message', 'unread_count']);

        $this->actingAs($admin)
            ->patchJson('/notifications/read-all')
            ->assertOk()
            ->assertJsonPath('unread_count', 0);

        // 8. PWA Manifest, DeepLink Association, Webhook Server Command & Deployment Script/Guide
        $manifest = json_decode((string) file_get_contents(public_path('manifest.webmanifest')), true);
        $this->assertSame('standalone', $manifest['display'] ?? null);
        $this->assertSame('preferred', $manifest['handle_links'] ?? null);
        $this->assertContains('camera', $manifest['permissions'] ?? []);
        $this->assertContains('geolocation', $manifest['permissions'] ?? []);

        $this->artisan('inventory:webhook-server --once')->assertExitCode(0);
        $this->postJson('/api/webhooks/receiver', ['event' => 'test.ping'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertFileExists(base_path('scripts/deploy.sh'));
        $this->assertFileExists(base_path('docs/DEPLOYMENT.md'));
        $makefile = (string) file_get_contents(base_path('Makefile'));
        $runDevSh = (string) file_get_contents(base_path('docker/run-dev.sh'));
        $this->assertStringContainsString('inventory:webhook-server', $runDevSh);
        $this->assertStringContainsString('deploy:', $makefile);
    }
}
