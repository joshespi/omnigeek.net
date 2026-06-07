<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PostMediaHandler
{
    /**
     * @return array{path: string, type: string} the stored path and 'image'|'video'
     */
    public static function store(UploadedFile $file): array
    {
        $isVideo = str_starts_with((string) $file->getMimeType(), 'video/');

        return [
            'path' => $isVideo
                ? $file->store('uploads', 'public')
                : ImageProcessor::compress($file, 'uploads', 1600),
            'type' => $isVideo ? 'video' : 'image',
        ];
    }

    public static function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
