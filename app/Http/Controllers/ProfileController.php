<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $rawPhone = $request->input('phone');
        $rawCountry = (string) $request->input('phone_country_dial', $request->input('phone_country_code', '+62'));
        $phoneCountryDial = str_starts_with($rawCountry, '+') ? $rawCountry : '+'.$rawCountry;
        $normalizedPhone = User::normalizePhoneNumber(is_string($rawPhone) ? $rawPhone : null, $phoneCountryDial);

        if ($normalizedPhone !== null) {
            $request->merge(['phone' => $normalizedPhone]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => [
                'nullable',
                'string',
                'max:25',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value !== null && $value !== '' && ! User::isValidPhoneNumber((string) $value)) {
                        $fail('Format nomor telepon tidak valid. Gunakan format Indonesia (08xx / +628xx) atau internasional (+KodeNegara).');
                    }
                },
            ],
            'gender' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'cropped_avatar' => ['nullable', 'string'],
        ], [
            'avatar.max' => 'Ukuran foto profil maksimal 8 MB (8192 KB).',
            'avatar.image' => 'File foto profil harus berupa gambar yang valid (JPG, PNG, WEBP).',
        ]);

        unset($data['cropped_avatar']);

        if (! empty($data['birth_date'])) {
            try {
                $data['birth_date'] = Carbon::parse($data['birth_date'])->format('Y-m-d');
            } catch (\Throwable $e) {
                unset($data['birth_date']);
            }
        }

        $croppedAvatarData = (string) $request->input('cropped_avatar', '');

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->storeAvatar($request->file('avatar'), $user);
        } elseif (str_starts_with($croppedAvatarData, 'data:image/')) {
            $storedCrop = $this->storeCroppedAvatarDataUrl($croppedAvatarData, $user);
            if ($storedCrop !== null) {
                $data['avatar'] = $storedCrop;
            }
        }

        $user->fill($data);
        $user->save();

        return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.');
    }

    private function storeCroppedAvatarDataUrl(string $dataUrl, User $user): ?string
    {
        if (! preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,(.+)$/i', $dataUrl, $matches)) {
            return null;
        }

        $ext = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
        $binary = base64_decode($matches[2], true);

        if ($binary === false || strlen($binary) > 8 * 1024 * 1024) {
            return null;
        }

        $path = 'avatars/avatar-'.$user->id.'-'.time().'.'.$ext;
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    private function storeAvatar($file, $user): string
    {
        if (app()->runningUnitTests() || empty(env('CLOUDINARY_CLOUD_NAME'))) {
            return Storage::disk('public')->putFile('avatars', $file);
        }

        try {
            $avatarUrl = $this->uploadAvatarToCloudinary($file);

            if (! empty($avatarUrl)) {
                return $avatarUrl;
            }
        } catch (\Throwable $e) {
            // Fallback ke local storage jika Cloudinary gagal
        }

        return Storage::disk('public')->putFile('avatars', $file);
    }

    private function uploadAvatarToCloudinary($file)
    {
        $response = Http::withOptions(['verify' => false])
            ->asMultipart()
            ->post('https://api.cloudinary.com/v1_1/'.env('CLOUDINARY_CLOUD_NAME').'/image/upload', [
                'file' => fopen($file->getRealPath(), 'r'),
                'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET', 'q46tbsqz'),
            ]);

        if ($response->successful()) {
            return $response->json()['secure_url'];
        }

        throw new \Exception('Gagal upload: '.$response->body());
    }
}
