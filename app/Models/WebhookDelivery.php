<?php

/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — Outgoing Webhook Delivery Audit Model
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    protected $fillable = [
        'webhook_endpoint_id',
        'event_name',
        'request_payload',
        'response_status',
        'response_body',
        'duration_ms',
        'status',
        'triggered_by',
    ];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_status' => 'integer',
            'duration_ms' => 'integer',
        ];
    }

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class, 'webhook_endpoint_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
