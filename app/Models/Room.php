<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class Room extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'location',
        'person_in_charge',
        'description',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected $activityLabel = 'Ruangan';
    protected $activityNameField = 'name';
}
