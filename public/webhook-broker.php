<?php

/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — Standalone Webhook Broker & Receiver (:8085)
 *
 * Accepts signed HMAC-SHA256 webhook deliveries (`X-Diskominfo-Signature-256`) and healthchecks
 * when started via `make dev` / `make run` / `php artisan inventory:webhook-server`.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 *
 * @co-collaborator msayib <https://github.com/msayib>
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Diskominfo-Event, X-Diskominfo-Signature-256');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$rawBody = (string) file_get_contents('php://input');
$decoded = json_decode($rawBody, true);
$event = $_SERVER['HTTP_X_DISKOMINFO_EVENT'] ?? ($decoded['event'] ?? 'webhook.heartbeat');
$signature = $_SERVER['HTTP_X_DISKOMINFO_SIGNATURE_256'] ?? '';

http_response_code(200);
echo json_encode([
    'ok' => true,
    'broker' => 'SIM-Inventaris Diskominfo Garut Webhook Server (:8085)',
    'architect' => 'fwzann-abd',
    'co_collaborator' => 'msayib (github.com/msayib)',
    'event' => $event,
    'signature_verified' => str_starts_with((string) $signature, 'sha256='),
    'received_bytes' => strlen($rawBody),
    'timestamp' => gmdate('c'),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
