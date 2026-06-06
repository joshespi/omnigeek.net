<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageProcessor
{
    public static function compress(UploadedFile $file, string $dir, int $maxWidth, int $quality = 82): string
    {
        $extension = $file->getMimeType() === 'image/png' ? 'png' : 'jpg';
        $path = $dir.'/'.Str::random(40).'.'.$extension;

        self::encodeTo($file, $path, $maxWidth, $quality);

        return $path;
    }

    public static function compressTo(UploadedFile $file, string $path, int $maxWidth, int $quality = 82): void
    {
        self::encodeTo($file, $path, $maxWidth, $quality);
    }

    private static function encodeTo(UploadedFile $file, string $path, int $maxWidth, int $quality): void
    {
        $image = (new ImageManager(Driver::class))
            ->decodePath($file->getRealPath())
            ->orient()
            ->scaleDown($maxWidth);

        $encoded = pathinfo($path, PATHINFO_EXTENSION) === 'png'
            ? $image->encodeUsingFileExtension('png')
            : $image->encodeUsingFileExtension('jpg', quality: $quality);

        Storage::disk('public')->put($path, (string) $encoded);
    }
}
