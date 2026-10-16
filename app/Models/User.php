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

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'gender', 'birth_date', 'address', 'bio', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPERADMIN = 'superadmin';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_USER = 'user';

    public function mutations(): HasMany
    {
        return $this->hasMany(Mutation::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function getUsernameAttribute($value): string
    {
        if (! empty($value)) {
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

        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://') || str_starts_with($avatar, 'data:image/')) {
            return $avatar;
        }

        if (str_starts_with($avatar, 'public/')) {
            $avatar = substr($avatar, 7);
        }

        if (str_starts_with($avatar, 'storage/')) {
            return '/'.ltrim($avatar, '/');
        }

        return '/storage/'.ltrim($avatar, '/');
    }

    public function isAdmin(): bool
    {
        $role = strtolower((string) $this->role);

        return in_array($role, ['admin', 'administrator', 'superadmin'], true);
    }

    public function isSuperAdmin(): bool
    {
        return strtolower((string) $this->role) === 'superadmin';
    }

    public function roleLabel(): string
    {
        if ($this->isSuperAdmin()) {
            return 'Super Admin';
        }

        if ($this->isAdmin()) {
            return 'Admin Inventaris';
        }

        return 'Operator';
    }

    public function canCreateProduct(): bool
    {
        return $this->isAdmin();
    }

    public function setRoleAttribute($value): void
    {
        $role = strtolower(trim((string) $value));

        if ($role === 'administrator') {
            $role = 'admin';
        }

        $this->attributes['role'] = $role;
    }

    public static function normalizePhoneNumber(?string $raw, ?string $defaultDialCode = '+62'): ?string
    {
        if ($raw === null) {
            return null;
        }

        $trimmed = trim($raw);
        if ($trimmed === '') {
            return null;
        }

        $cleaned = preg_replace('/[^\d+]/', '', $trimmed) ?? '';
        if ($cleaned === '' || $cleaned === '+') {
            return null;
        }

        if (str_starts_with($cleaned, '00')) {
            $cleaned = '+'.substr($cleaned, 2);
        }

        $dial = $defaultDialCode ? ('+'.ltrim(preg_replace('/\D/', '', $defaultDialCode) ?: '62', '0')) : '+62';

        if (str_starts_with($cleaned, '+')) {
            $digits = preg_replace('/\D/', '', substr($cleaned, 1)) ?? '';
            if (str_starts_with($digits, '620')) {
                $digits = '62'.ltrim(substr($digits, 2), '0');
            }

            return $digits !== '' ? '+'.$digits : null;
        }

        if (str_starts_with($cleaned, '628') || str_starts_with($cleaned, '622')) {
            return '+'.$cleaned;
        }

        if (str_starts_with($cleaned, '0')) {
            $national = ltrim($cleaned, '0');

            return $national !== '' ? $dial.$national : null;
        }

        if ($dial === '+62' && str_starts_with($cleaned, '8') && strlen($cleaned) >= 8 && strlen($cleaned) <= 13) {
            return '+62'.$cleaned;
        }

        $knownPrefixes = ['62', '60', '65', '44', '61', '81', '82', '86', '91', '49', '31', '33', '966', '971', '1'];
        foreach ($knownPrefixes as $prefix) {
            if (str_starts_with($cleaned, $prefix) && strlen($cleaned) >= strlen($prefix) + 7) {
                return '+'.$cleaned;
            }
        }

        return $dial.ltrim($cleaned, '0');
    }

    public static function isValidPhoneNumber(?string $normalized): bool
    {
        if ($normalized === null || $normalized === '') {
            return true;
        }

        if (! preg_match('/^\+[1-9]\d{6,14}$/', $normalized)) {
            return false;
        }

        if (str_starts_with($normalized, '+62')) {
            $national = substr($normalized, 3);

            return (bool) preg_match('/^[2-9]\d{7,12}$/', $national);
        }

        if (str_starts_with($normalized, '+65')) {
            $national = substr($normalized, 3);

            return (bool) preg_match('/^[3689]\d{7}$/', $national);
        }

        if (str_starts_with($normalized, '+1')) {
            $national = substr($normalized, 2);

            return (bool) preg_match('/^[2-9]\d{9}$/', $national);
        }

        return true;
    }

    public function getPhoneMetaAttribute(): array
    {
        $raw = (string) ($this->phone ?? '');
        $normalized = self::normalizePhoneNumber($raw) ?? '';

        if ($normalized === '') {
            return [
                'raw' => '',
                'e164' => '',
                'country' => 'ID',
                'dial' => '+62',
                'name' => 'Indonesia',
                'provider' => null,
                'national' => '',
                'formatted' => '',
            ];
        }

        $countries = [
            ['dial' => '+966', 'code' => 'SA', 'name' => 'Arab Saudi'],
            ['dial' => '+971', 'code' => 'AE', 'name' => 'Uni Emirat Arab'],
            ['dial' => '+62', 'code' => 'ID', 'name' => 'Indonesia'],
            ['dial' => '+60', 'code' => 'MY', 'name' => 'Malaysia'],
            ['dial' => '+65', 'code' => 'SG', 'name' => 'Singapura'],
            ['dial' => '+44', 'code' => 'GB', 'name' => 'Inggris (UK)'],
            ['dial' => '+61', 'code' => 'AU', 'name' => 'Australia'],
            ['dial' => '+81', 'code' => 'JP', 'name' => 'Jepang'],
            ['dial' => '+82', 'code' => 'KR', 'name' => 'Korea Selatan'],
            ['dial' => '+86', 'code' => 'CN', 'name' => 'Tiongkok'],
            ['dial' => '+91', 'code' => 'IN', 'name' => 'India'],
            ['dial' => '+49', 'code' => 'DE', 'name' => 'Jerman'],
            ['dial' => '+31', 'code' => 'NL', 'name' => 'Belanda'],
            ['dial' => '+33', 'code' => 'FR', 'name' => 'Prancis'],
            ['dial' => '+1', 'code' => 'US', 'name' => 'Amerika Serikat / Kanada'],
        ];

        $matched = ['dial' => '+62', 'code' => 'INTL', 'name' => 'Internasional'];
        $national = ltrim($normalized, '+');

        foreach ($countries as $country) {
            if (str_starts_with($normalized, $country['dial'])) {
                $matched = $country;
                $national = substr($normalized, strlen($country['dial']));
                break;
            }
        }

        if ($matched['code'] === 'INTL') {
            $dialGuess = '+'.substr($national, 0, min(3, max(1, strlen($national) - 7)));
            $matched['dial'] = $dialGuess;
            $national = substr($normalized, strlen($dialGuess));
        }

        $provider = null;
        if ($matched['code'] === 'ID') {
            $prefix4 = substr('0'.$national, 0, 4);
            $provider = match (true) {
                in_array($prefix4, ['0811', '0812', '0813', '0821', '0822', '0823', '0851', '0852', '0853'], true) => 'Telkomsel',
                in_array($prefix4, ['0814', '0815', '0816', '0855', '0856', '0857', '0858', '0895', '0896', '0897', '0898', '0899'], true) => 'Indosat / Tri',
                in_array($prefix4, ['0817', '0818', '0819', '0859', '0877', '0878', '0831', '0832', '0833', '0838'], true) => 'XL / Axis',
                in_array($prefix4, ['0881', '0882', '0883', '0884', '0885', '0886', '0887', '0888', '0889'], true) => 'Smartfren',
                str_starts_with($national, '2') => 'Telepon Kantor / PSTN',
                default => 'Seluler Indonesia',
            };
        }

        if ($matched['code'] === 'ID' && strlen($national) >= 8) {
            $part1 = substr($national, 0, 3);
            $part2 = substr($national, 3, 4);
            $part3 = substr($national, 7);
            $formattedNational = implode('-', array_filter([$part1, $part2, $part3], fn ($p) => $p !== ''));
        } else {
            $chunks = str_split($national, 4);
            $formattedNational = implode('-', $chunks);
        }

        return [
            'raw' => $raw,
            'e164' => $normalized,
            'country' => $matched['code'],
            'iso' => $matched['code'],
            'dial' => $matched['dial'],
            'name' => $matched['name'],
            'provider' => $provider,
            'operator' => $provider,
            'national' => $national,
            'formatted' => trim($matched['dial'].' '.$formattedNational),
        ];
    }

    public function getFormattedPhoneAttribute(): string
    {
        $meta = $this->phone_meta;

        return $meta['formatted'] !== '' ? $meta['formatted'] : '';
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
