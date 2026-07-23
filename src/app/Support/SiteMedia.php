<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SiteMedia
{
    public const LOGO = 'site/logo.png';
    public const OG_DEFAULT = 'site/og-default.png';

    public static function logoUrl(): ?string
    {
        return self::urlOrNull(self::LOGO);
    }

    public static function ogDefaultUrl(): string
    {
        return self::urlOrNull(self::OG_DEFAULT) ?? asset('icons/web-app-manifest-512x512.png');
    }

    public static function store(string $path, UploadedFile $file, int $maxWidth): void
    {
        // compressTo() overwrites $path in place (Storage::put), so a failed encode
        // never touches the existing file — no upfront delete needed here.
        ImageProcessor::compressTo($file, $path, $maxWidth);
        Cache::forget(self::cacheKey($path));
    }

    public static function delete(string $path): void
    {
        Storage::disk('public')->delete($path);
        Cache::forget(self::cacheKey($path));
    }

    // These paths only change when an admin re-uploads via AdminMedia, but the
    // URL is read on every page (logo, OG image) — cache the stat check.
    protected static function urlOrNull(string $path): ?string
    {
        return Cache::rememberForever(self::cacheKey($path), function () use ($path) {
            $disk = Storage::disk('public');

            if (! $disk->exists($path)) {
                return null;
            }

            $bust = config('app.version').'-'.$disk->lastModified($path);

            return $disk->url($path).'?v='.$bust;
        });
    }

    protected static function cacheKey(string $path): string
    {
        return 'site-media-url:'.$path;
    }
}
