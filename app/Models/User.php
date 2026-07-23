<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'gender', 'birth_date', 'address', 'bio', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function mutations(): HasMany
    {
        return $this->hasMany(Mutation::class);
    }

    public function getUsernameAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }

        $emailLocalPart = explode('@', (string) $this->email)[0] ?? '';

        return $emailLocalPart !== '' ? $emailLocalPart : ($this->name ?? 'Administrator');
    }

    public function getAvatarUrlAttribute(): string
    {
        if (empty($this->avatar)) {
            return '';
        }

        $avatar = (string) $this->avatar;

        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            return $avatar;
        }

        if (str_starts_with($avatar, 'public/')) {
            $avatar = substr($avatar, 7);
        }

        if (str_starts_with($avatar, 'storage/')) {
            return asset($avatar);
        }

        return Storage::url($avatar);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }
}
