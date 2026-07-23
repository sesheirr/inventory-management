<?php

namespace App\Http\Controllers;

use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if (!empty($data['birth_date'])) {
            try {
                $data['birth_date'] = \Carbon\Carbon::parse($data['birth_date'])->format('Y-m-d');
            } catch (\Throwable $e) {
                unset($data['birth_date']);
            }
        }

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->storeAvatar($request->file('avatar'), $user);
        }

        $user->fill($data);
        $user->save();

        return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.');
    }

    private function storeAvatar($file, $user): string
    {
        $avatarUrl = $this->uploadAvatarToCloudinary($file);

        if (!empty($avatarUrl)) {
            $this->deletePreviousLocalAvatar($user);

            return $avatarUrl;
        }

        $storedPath = $file->store('avatars', 'public');
        $this->deletePreviousLocalAvatar($user);

        return $storedPath;
    }

    private function uploadAvatarToCloudinary($file): ?string
    {
        $cloudinaryUrl = env('CLOUDINARY_URL');

        if (empty($cloudinaryUrl)) {
            return null;
        }

        try {
            $cloudinary = $this->getCloudinary();
            $uploadedFile = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                'folder' => 'inventory-profiles',
                'resource_type' => 'image',
            ]);

            return $uploadedFile['secure_url'] ?? $uploadedFile['url'] ?? null;
        } catch (\Throwable $e) {
            Log::warning('Gagal mengunggah avatar ke Cloudinary.', ['exception' => $e->getMessage()]);

            return null;
        }
    }

    private function deletePreviousLocalAvatar($user): void
    {
        if (empty($user->avatar) || !is_string($user->avatar)) {
            return;
        }

        $avatarPath = $user->avatar;

        if (str_starts_with($avatarPath, 'http://') || str_starts_with($avatarPath, 'https://')) {
            return;
        }

        $avatarPath = ltrim($avatarPath, '/');
        $avatarPath = preg_replace('#^public/#', '', $avatarPath);

        if ($avatarPath !== '' && Storage::disk('public')->exists($avatarPath)) {
            Storage::disk('public')->delete($avatarPath);
        }
    }

    private function getCloudinary(): Cloudinary
    {
        $cloudinaryUrl = env('CLOUDINARY_URL');

        if (empty($cloudinaryUrl)) {
            throw new \RuntimeException('CLOUDINARY_URL is not set. Please configure Cloudinary in your .env');
        }

        $parts = parse_url($cloudinaryUrl);

        if (!$parts || empty($parts['scheme']) || ($parts['scheme'] !== 'cloudinary')) {
            throw new \RuntimeException('Invalid CLOUDINARY_URL format.');
        }

        $host = $parts['host'] ?? '';
        $user = $parts['user'] ?? '';
        $pass = $parts['pass'] ?? '';

        if ($host === '' || $user === '' || $pass === '') {
            throw new \RuntimeException('Incomplete Cloudinary credentials in CLOUDINARY_URL.');
        }

        return new Cloudinary([
            'cloud' => $host,
            'api_key' => $user,
            'api_secret' => $pass,
        ]);
    }
}
