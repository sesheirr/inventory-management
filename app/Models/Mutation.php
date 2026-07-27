<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mutation extends Model
{
    use HasFactory;
    use \App\Traits\LogsActivity;

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'from_room_id',
        'to_room_id',
        'mutation_date',
        'note',
    ];

    protected $casts = [
        'mutation_date' => 'datetime',
    ];

    // Activity log defaults for Mutation
    protected $activityLabel = 'Mutasi';
    protected $activityNameField = 'id';

    // Mutation needs a custom description because it doesn't have a name field
    public function buildActivityDescription(string $action): string
    {
        $productName = $this->product?->name ?? ('#' . ($this->product_id ?? $this->getKey()));
        $typeLabel = ucfirst(str_replace('_', ' ', (string) $this->type));
        return sprintf('Mutasi %s untuk %s: %s', strtolower($typeLabel), $productName, $action);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'from_room_id');
    }

    public function toRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'to_room_id');
    }
}
