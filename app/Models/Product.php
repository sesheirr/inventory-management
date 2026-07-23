<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'category_id',
        'room_id',
        'room',
        'subcategory',
        'edition',
        'description',
        'stock',
        'price',
        'status',
        'image',
        'image_public_id',
        'kode_barang',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(Mutation::class);
    }
}
