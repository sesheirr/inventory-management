<?php

/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — Outgoing Webhook Endpoint Model
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookEndpoint extends Model
{
    protected $fillable = [
        'name',
        'target_url',
        'secret',
        'events',
        'is_active',
        'last_triggered_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'is_active' => 'boolean',
            'last_triggered_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }
}
