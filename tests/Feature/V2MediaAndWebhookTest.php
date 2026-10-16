<?php

/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Multi-Image FilePond & Admin Webhook Tests
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Room;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class V2MediaAndWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_async_filepond_temporary_upload_and_multi_image_product_gallery_work(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $category = Category::create(['name' => 'Perangkat Jaringan']);
        $room = Room::create(['name' => 'Ruang NOC', 'code' => 'NOC-01']);

        // 1. Upload 2 temporary images via FilePond async endpoint
        $file1 = UploadedFile::fake()->image('router-front.jpg', 800, 600);
        $file2 = UploadedFile::fake()->image('router-back.png', 800, 600);

        $tmpRes1 = $this->actingAs($admin)->postJson(route('uploads.tmp.store'), [
            'file' => $file1,
        ]);
        $tmpRes1->assertOk()->assertJsonStructure(['id', 'path', 'url', 'filename']);
        $tmpPath1 = $tmpRes1->json('path');

        $tmpRes2 = $this->actingAs($admin)->postJson(route('uploads.tmp.store'), [
            'file' => $file2,
        ]);
        $tmpRes2->assertOk();
        $tmpPath2 = $tmpRes2->json('path');

        Storage::disk('public')->assertExists($tmpPath1);
        Storage::disk('public')->assertExists($tmpPath2);

        // 2. Create a product attaching both FilePond temporary uploads + direct multi-files
        $file3 = UploadedFile::fake()->image('router-label.jpg', 600, 600);

        $storeRes = $this->actingAs($admin)->post(route('products.store'), [
            'name' => 'Core Router MikroTik CCR2116',
            'category_id' => $category->id,
            'room_id' => $room->id,
            'stock' => 4,
            'status' => 'active',
            'uploaded_tmp_files' => [$tmpPath1, $tmpPath2],
            'images' => [$file3],
        ]);

        $storeRes->assertRedirect(route('products.index'));

        $product = Product::where('name', 'Core Router MikroTik CCR2116')->firstOrFail();
        $this->assertGreaterThanOrEqual(3, count($product->gallery_urls));
        $this->assertNotNull($product->image_url);

        // 3. Test deleting a temporary upload
        $fileTempDelete = UploadedFile::fake()->image('scratch.jpg', 300, 300);
        $tmpResDel = $this->actingAs($admin)->postJson(route('uploads.tmp.store'), [
            'file' => $fileTempDelete,
        ]);
        $delPath = $tmpResDel->json('path');
        Storage::disk('public')->assertExists($delPath);

        $delRes = $this->actingAs($admin)->deleteJson(route('uploads.tmp.destroy'), [
            'path' => $delPath,
        ]);
        $delRes->assertOk()->assertJsonPath('deleted', true);
        Storage::disk('public')->assertMissing($delPath);
    }

    public function test_webhooks_module_is_strictly_restricted_to_admin_and_superadmin(): void
    {
        $regularUser = User::factory()->create(['role' => User::ROLE_USER]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        // Regular user MUST get 403 on all webhook endpoints
        $this->actingAs($regularUser)->get(route('webhooks.index'))->assertForbidden();
        $this->actingAs($regularUser)->post(route('webhooks.store'), [
            'name' => 'Unauthorized Hook',
            'target_url' => 'https://example.com/webhook',
            'events' => ['product.created'],
        ])->assertForbidden();

        // Admin can access and create a webhook endpoint
        $indexRes = $this->actingAs($admin)->get(route('webhooks.index'));
        $indexRes->assertOk()
            ->assertSee('Webhook', false);

        $storeRes = $this->actingAs($admin)->post(route('webhooks.store'), [
            'name' => 'SI-ASET Diskominfo Receiver',
            'target_url' => 'https://integrasi.garutkab.go.id/webhooks/inventaris',
            'secret' => 'garut-hmac-secret-key-2026',
            'events' => ['product.created', 'mutation.approved', 'stock.low'],
        ]);
        $storeRes->assertRedirect(route('webhooks.index'));

        $endpoint = WebhookEndpoint::where('name', 'SI-ASET Diskominfo Receiver')->firstOrFail();
        $this->assertTrue($endpoint->is_active);
        $this->assertContains('mutation.approved', $endpoint->events);

        // Ping webhook using Http::fake and verify HMAC-SHA256 signature & delivery audit log
        Http::fake([
            'integrasi.garutkab.go.id/*' => Http::response(['status' => 'received'], 200),
        ]);

        $pingRes = $this->actingAs($admin)->post(route('webhooks.ping', $endpoint));
        $pingRes->assertRedirect(route('webhooks.index'));

        $delivery = WebhookDelivery::where('webhook_endpoint_id', $endpoint->id)->first();
        $this->assertNotNull($delivery);
        $this->assertSame('inventory.webhook.ping', $delivery->event_name);
        $this->assertSame(200, $delivery->response_status);
        $this->assertSame('delivered', $delivery->status);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'integrasi.garutkab.go.id/webhooks/inventaris')
                && $request->hasHeader('X-Diskominfo-Signature-256')
                && str_starts_with($request->header('X-Diskominfo-Signature-256')[0], 'sha256=');
        });
    }
}
