<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use RuntimeException;

final class ImageUploadSecurity
{
    /** @var array<string, string> */
    private const EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    private const MAX_PIXELS = 40_000_000;

    public static function inspect(UploadedFile $file): string
    {
        $path = $file->getRealPath();
        if (! is_string($path) || ! is_file($path)) {
            throw new RuntimeException('Berkas upload tidak dapat dibaca.');
        }

        return self::inspectPath($path);
    }

    public static function inspectPath(string $path): string
    {
        $contents = file_get_contents($path);
        if (! is_string($contents) || $contents === '') {
            throw new RuntimeException('Berkas gambar kosong atau tidak dapat dibaca.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($path);
        $imageInfo = @getimagesize($path);
        $detectedMime = is_array($imageInfo) ? (string) ($imageInfo['mime'] ?? '') : '';

        if (! isset(self::EXTENSIONS[$mime]) || $detectedMime !== $mime) {
            throw new RuntimeException('Isi berkas bukan gambar JPG, PNG, atau WEBP yang valid.');
        }

        $width = (int) ($imageInfo[0] ?? 0);
        $height = (int) ($imageInfo[1] ?? 0);
        if ($width < 1 || $height < 1 || $width * $height > self::MAX_PIXELS) {
            throw new RuntimeException('Dimensi gambar tidak valid atau terlalu besar.');
        }

        if (preg_match('/<\?php|<script\b|__halt_compiler|phar:\/\//i', $contents) === 1) {
            throw new RuntimeException('Berkas gambar memuat payload aktif yang tidak diizinkan.');
        }

        self::assertNoTrailingPayload($contents, $mime);

        return self::EXTENSIONS[$mime];
    }

    private static function assertNoTrailingPayload(string $contents, string $mime): void
    {
        $valid = match ($mime) {
            'image/jpeg' => self::jpegEndsCleanly($contents),
            'image/png' => str_ends_with($contents, "\x00\x00\x00\x00IEND\xAE\x42\x60\x82"),
            'image/webp' => strlen($contents) >= 12
                && substr($contents, 0, 4) === 'RIFF'
                && substr($contents, 8, 4) === 'WEBP'
                && unpack('Vsize', substr($contents, 4, 4))['size'] + 8 === strlen($contents),
            default => false,
        };

        if (! $valid) {
            throw new RuntimeException('Struktur akhir gambar tidak valid atau memuat data tambahan.');
        }
    }

    private static function jpegEndsCleanly(string $contents): bool
    {
        $trimmed = rtrim($contents, "\x00\x09\x0A\x0D\x20");

        return str_ends_with($trimmed, "\xFF\xD9");
    }
}
