<?php

/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — Filament-Grade Async FilePond Media Uploader
 *
 * Handles asynchronous temporary file uploads (`POST /uploads/tmp`) and cleanup
 * (`DELETE /uploads/tmp`) before attaching to Spatie MediaLibrary collections.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaUploadController extends Controller
{
    public function storeTemporary(Request $request): JsonResponse
    {
        $fileKey = $request->hasFile('file') ? 'file' : ($request->hasFile('image') ? 'image' : 'file');

        $request->validate([
            $fileKey => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        /** @var UploadedFile $file */
        $file = $request->file($fileKey);
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $id = (string) Str::uuid();
        $filename = $id.'.'.$ext;

        $path = $file->storeAs('tmp/uploads', $filename, 'public');

        return response()->json([
            'id' => $id,
            'path' => $path,
            'url' => '/storage/'.ltrim((string) $path, '/'),
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    public function destroyTemporary(Request $request): JsonResponse
    {
        $rawPath = trim((string) ($request->input('path') ?: $request->getContent()));
        if ($rawPath !== '' && str_starts_with($rawPath, 'tmp/uploads/') && ! str_contains($rawPath, '..')) {
            Storage::disk('public')->delete($rawPath);
        }

        return response()->json([
            'deleted' => true,
        ]);
    }
}
