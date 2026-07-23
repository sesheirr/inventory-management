<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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

        if (empty($avatarUrl)) {
            throw new \RuntimeException('Gagal mengunggah foto profil ke Cloudinary. Periksa konfigurasi CLOUDINARY_URL atau variabel Cloudinary Anda.');
        }

        return $avatarUrl;
    }

    private function uploadAvatarToCloudinary($file)
    {
        $response = Http::asMultipart()->post('https://api.cloudinary.com/v1_1/' . env('CLOUDINARY_CLOUD_NAME') . '/image/upload', [
            'file' => fopen($file->getRealPath(), 'r'),
            'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET', 'q46tbsqz'),
        ]);

        if ($response->successful()) {
            return $response->json()['secure_url'];
        }

        throw new \Exception('Gagal upload ke Cloudinary: ' . $response->body());
    }
}
