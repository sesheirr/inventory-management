<?php

/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Mutations, Profile, Notifications & Admin Observability Tests
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\Room;
use App\Models\User;
use App\Models\WebhookEndpoint;
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
    }
}
