<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class PublicMedia
{
    public static function store(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'public');

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
}
