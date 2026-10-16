<?php

/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — Official Laravel HTTP Webhook Dispatcher
 *
 * Signs outgoing JSON payloads with HMAC-SHA256 (`X-Diskominfo-Signature-256`)
 * and records latency + response status in `webhook_deliveries`.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

namespace App\Services;

use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Http;

class WebhookDispatcher
{
    /**
     * Dispatch an event payload to a specific WebhookEndpoint.
     *
     * @param  array<string, mixed>  $data
     */
    public function dispatchToEndpoint(
        WebhookEndpoint $endpoint,
        string $eventName,
        array $data,
        ?User $actor = null
    ): WebhookDelivery {
        $payload = [
            'event' => $eventName,
            'timestamp' => now()->toIso8601String(),
            'source' => 'SIM-Inventaris Diskominfo Kabupaten Garut',
            'architect' => 'fwzann-abd',
            'data' => $data,
        ];

        $rawJson = (string) json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $signature = 'sha256='.hash_hmac('sha256', $rawJson, (string) $endpoint->secret);

        $startedAt = microtime(true);
        $statusCode = null;
        $responseBody = null;
        $deliveryStatus = 'failed';

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'DiskominfoGarut-Webhook/2.0 (+https://github.com/fwzann-abd)',
                    'X-Diskominfo-Event' => $eventName,
                    'X-Diskominfo-Signature-256' => $signature,
                ])
                ->withBody($rawJson, 'application/json')
                ->post($endpoint->target_url);

            $statusCode = $response->status();
            $responseBody = mb_substr((string) $response->body(), 0, 2000);
            $deliveryStatus = $response->successful() ? 'delivered' : 'failed';
        } catch (\Throwable $e) {
            $responseBody = mb_substr($e->getMessage(), 0, 2000);
            $deliveryStatus = 'failed';
        }

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        $endpoint->update([
            'last_triggered_at' => now(),
        ]);

        return WebhookDelivery::create([
            'webhook_endpoint_id' => $endpoint->id,
            'event_name' => $eventName,
            'request_payload' => $payload,
            'response_status' => $statusCode,
            'response_body' => $responseBody,
            'duration_ms' => max(1, $durationMs),
            'status' => $deliveryStatus,
            'triggered_by' => $actor?->id,
        ]);
    }

    /**
     * Broadcast an event to all active endpoints subscribed to `$eventName`.
     *
     * @param  array<string, mixed>  $data
     */
    public function broadcast(string $eventName, array $data, ?User $actor = null): void
    {
        try {
            $endpoints = WebhookEndpoint::where('is_active', true)->get();
            foreach ($endpoints as $endpoint) {
                $subscribed = is_array($endpoint->events) ? $endpoint->events : [];
                if (in_array($eventName, $subscribed, true) || in_array('*', $subscribed, true)) {
                    $this->dispatchToEndpoint($endpoint, $eventName, $data, $actor);
                }
            }
        } catch (\Throwable $e) {
            // Non-blocking broadcast
        }
    }
}
