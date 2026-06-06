<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SiteMedia
{
    public const LOGO = 'site/logo.png';
    public const OG_DEFAULT = 'site/og-default.png';

    public static function logoUrl(): ?string
    {
        return self::urlOrNull(self::LOGO);
    }

    public static function ogDefaultUrl(): ?string
    {
        return self::urlOrNull(self::OG_DEFAULT);
    }

    public static function store(string $path, UploadedFile $file, int $maxWidth): void
    {
        Storage::disk('public')->delete($path);
        ImageProcessor::compressTo($file, $path, $maxWidth);
    }

    public static function delete(string $path): void
    {
        Storage::disk('public')->delete($path);
    }

    protected static function urlOrNull(string $path): ?string
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        $bust = config('app.version').'-'.$disk->lastModified($path);

        return $disk->url($path).'?v='.$bust;
    }
}
