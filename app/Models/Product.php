<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Storage;
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

    public function getImageUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('images');
        if (!$media) {
            return null;
        }

        $relativePath = $media->id . '/' . $media->file_name;
        try {
            if (Storage::disk($media->disk)->exists($relativePath)) {
                return '/storage/' . $media->id . '/' . rawurlencode($media->file_name);
            }
        } catch (\Throwable $e) {
            // Fallback below
        }

        $legacyPath = $media->getCustomProperty('legacy_path');
        if (is_string($legacyPath) && $legacyPath !== '') {
            try {
                if (Storage::disk('public')->exists($legacyPath)) {
                    return '/storage/' . ltrim($legacyPath, '/');
                }
            } catch (\Throwable $e) {
                // Fallback below
            }
        }

        $dataUrl = $media->getCustomProperty('data_url');
        if (is_string($dataUrl) && str_starts_with($dataUrl, 'data:image/')) {
            return $dataUrl;
        }

        return '/storage/' . $media->id . '/' . rawurlencode($media->file_name);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'out_of_stock' || (int) $this->stock <= 0) {
            return 'Stok Habis';
        }

        if ($this->status === 'active') {
            return 'Aktif';
        }

        return 'Tidak Aktif';
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(Mutation::class);
    }

    // Activity log labels
    protected $activityLabel = 'Barang';
    protected $activityNameField = 'name';
}
