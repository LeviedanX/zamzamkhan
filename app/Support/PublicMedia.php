<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class PublicMedia
{
    public static function store(UploadedFile $file, string $directory): string
    {
        try {
            $extension = ImageUploadSecurity::inspect($file);
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'file' => $exception->getMessage(),
            ]);
        }
        $filename = Str::lower((string) Str::ulid()).'.'.$extension;
        $path = Storage::disk('public')->putFileAs($directory, $file, $filename);

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('Berkas media gagal disimpan.');
        }

        return $path;
    }

    public static function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Resolusi sebuah path tersimpan (kolom *_path) menjadi URL publik untuk
     * preview di panel admin. Path bisa mengarah ke dua tempat berbeda:
     * aset tema bawaan di public/images (dari seeder) atau berkas yang
     * diupload lewat self::store() ke disk 'public' (storage/app/public).
     * Logika ini sengaja disamakan dengan
     * AppServiceProvider::publicAssetPath() yang dipakai untuk halaman
     * publik, supaya admin melihat pratinjau yang sama persis dengan apa
     * yang tampil ke pengunjung.
     */
    public static function previewUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalized = ltrim($path, '/');
        if (str_starts_with($normalized, 'images/') && is_file(public_path($normalized))) {
            return asset($normalized);
        }

        return Storage::disk('public')->exists($path) ? asset('storage/'.$path) : null;
    }
}
