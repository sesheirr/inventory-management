<?php

/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — Admin-Only Webhook Management Controller
 *
 * Strictly restricted to `admin` and `superadmin` roles via `middleware(['auth', 'admin'])`.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Services\WebhookDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class WebhookController extends Controller
{
    public const AVAILABLE_EVENTS = [
        'product.created' => 'Barang Baru Ditambahkan',
        'product.updated' => 'Data Barang Diperbarui',
        'stock.low' => 'Peringatan Stok Menipis',
        'mutation.created' => 'Pengajuan Mutasi Baru',
        'mutation.approved' => 'Mutasi Disetujui Admin',
        'mutation.rejected' => 'Mutasi Ditolak Admin',
    ];

    public function index(Request $request)
    {
        $endpoints = WebhookEndpoint::withCount('deliveries')
            ->latest()
            ->get();

        $recentDeliveries = WebhookDelivery::with(['endpoint:id,name,target_url', 'actor:id,name'])
            ->latest()
            ->take(25)
            ->get();

        $availableEvents = self::AVAILABLE_EVENTS;

        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Webhooks/Index', [
                'endpoints' => $endpoints,
                'recentDeliveries' => $recentDeliveries,
                'availableEvents' => $availableEvents,
            ]);
        }

        return view('webhooks.index', compact('endpoints', 'recentDeliveries', 'availableEvents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'target_url' => ['required', 'url', 'max:500'],
            'secret' => ['nullable', 'string', 'min:8', 'max:191'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['required', 'string'],
        ]);

        $endpoint = WebhookEndpoint::create([
            'name' => trim($validated['name']),
            'target_url' => trim($validated['target_url']),
            'secret' => ! empty($validated['secret'])
                ? trim((string) $validated['secret'])
                : 'whsec_'.Str::random(32),
            'events' => array_values(array_unique($validated['events'])),
            'is_active' => true,
            'created_by' => $request->user()?->id,
        ]);

        ActivityLog::record(
            action: 'CREATE',
            module: 'WEBHOOK',
            description: "Mendaftarkan endpoint webhook baru: {$endpoint->name} ({$endpoint->target_url}).",
            subjectType: WebhookEndpoint::class,
            subjectId: $endpoint->id,
            subjectLabel: $endpoint->name,
            properties: ['target_url' => $endpoint->target_url, 'events' => $endpoint->events],
            request: $request
        );

        return redirect()->route('webhooks.index')->with('success', 'Endpoint Webhook berhasil didaftarkan.');
    }

    public function toggle(Request $request, WebhookEndpoint $webhook)
    {
        $webhook->update([
            'is_active' => ! $webhook->is_active,
        ]);

        ActivityLog::record(
            action: 'UPDATE',
            module: 'WEBHOOK',
            description: ($webhook->is_active ? 'Mengaktifkan' : 'Menjeda')." endpoint webhook {$webhook->name}.",
            subjectType: WebhookEndpoint::class,
            subjectId: $webhook->id,
            subjectLabel: $webhook->name,
            properties: ['is_active' => $webhook->is_active],
            request: $request
        );

        return redirect()->route('webhooks.index')->with(
            'success',
            $webhook->is_active ? 'Webhook diaktifkan.' : 'Webhook dijeda sementara.'
        );
    }

    public function destroy(Request $request, WebhookEndpoint $webhook)
    {
        $name = $webhook->name;
        $id = $webhook->id;
        $webhook->delete();

        ActivityLog::record(
            action: 'DELETE',
            module: 'WEBHOOK',
            description: "Menghapus endpoint webhook {$name}.",
            subjectType: WebhookEndpoint::class,
            subjectId: $id,
            subjectLabel: $name,
            request: $request
        );

        return redirect()->route('webhooks.index')->with('success', 'Endpoint Webhook berhasil dihapus.');
    }

    public function ping(Request $request, WebhookEndpoint $webhook, WebhookDispatcher $dispatcher)
    {
        $delivery = $dispatcher->dispatchToEndpoint(
            $webhook,
            'inventory.webhook.ping',
            [
                'message' => 'Test ping dari SIM-Inventaris Diskominfo Kabupaten Garut',
                'endpoint_id' => $webhook->id,
                'endpoint_name' => $webhook->name,
            ],
            $request->user()
        );

        ActivityLog::record(
            action: 'WEBHOOK',
            module: 'WEBHOOK',
            description: "Mengirim test ping ke webhook {$webhook->name} (Status: {$delivery->status}).",
            subjectType: WebhookEndpoint::class,
            subjectId: $webhook->id,
            subjectLabel: $webhook->name,
            properties: [
                'status' => $delivery->status,
                'response_status' => $delivery->response_status,
                'duration_ms' => $delivery->duration_ms,
            ],
            request: $request
        );

        $flashType = $delivery->status === 'delivered' ? 'success' : 'error';
        $flashMsg = $delivery->status === 'delivered'
            ? "Ping berhasil dikirim ke {$webhook->name} (HTTP {$delivery->response_status}, {$delivery->duration_ms}ms)."
            : "Ping gagal dikirim ke {$webhook->name} (Status: {$delivery->status}).";

        return redirect()->route('webhooks.index')->with($flashType, $flashMsg);
    }

    /**
     * Built-in Webhook Receiver / Loopback Endpoint for local dev & automated verification.
     */
    public function receive(Request $request)
    {
        return response()->json([
            'ok' => true,
            'service' => 'SIM-Inventaris Diskominfo Garut Webhook Server',
            'event' => $request->header('X-Diskominfo-Event', 'unknown'),
            'signature_present' => $request->hasHeader('X-Diskominfo-Signature-256'),
            'received_at' => now()->toIso8601String(),
        ]);
    }
}
