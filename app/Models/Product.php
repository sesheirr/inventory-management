<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class Product extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'category',
        'category_id',
        'room_id',
        'subcategory',
        'edition',
        'description',
        'stock',
        'price',
        'status',
        'image',
        'image_public_id',
        'kode_barang',
        'barcode', // BARCODE FEATURE
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function getRoomNameAttribute(): ?string
    {
        if ($this->room_id !== null) {
            return $this->room()->first()?->name;
        }

        return $this->getAttribute('room');
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(Mutation::class);
    }

    // Activity log labels
    protected $activityLabel = 'Barang';
    protected $activityNameField = 'name';
}
