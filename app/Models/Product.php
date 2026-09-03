<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
class Product extends Model implements HasMedia
{
    use HasFactory;
    use LogsActivity;
    use InteractsWithMedia;

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

    public function getCategoryNameAttribute(): string
    {
        if ($this->category_id !== null) {
            $cat = $this->relationLoaded('category') ? $this->getRelation('category') : $this->category()->first();
            if ($cat instanceof Category) {
                return $cat->name;
            }
        }

        $rawCategory = $this->attributes['category'] ?? null;
        if (is_string($rawCategory) && trim($rawCategory) !== '') {
            return trim($rawCategory);
        }

        return 'Umum';
    }

    public function getRoomNameAttribute(): ?string
    {
        if ($this->room_id !== null) {
            $room = $this->relationLoaded('room') ? $this->getRelation('room') : $this->room()->first();
            if ($room instanceof Room) {
                return $room->name;
            }
        }

        $rawRoom = $this->attributes['room'] ?? null;
        return (is_string($rawRoom) && trim($rawRoom) !== '') ? trim($rawRoom) : null;
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(Mutation::class);
    }

    // Activity log labels
    protected $activityLabel = 'Barang';
    protected $activityNameField = 'name';
}
