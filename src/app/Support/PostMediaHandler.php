<?php

namespace App\Support;

use App\Models\Post;
use App\Models\PostMedia;
use Illuminate\Http\UploadedFile;

class PostMediaHandler
{
    /**
     * Store one uploaded file and attach it to the post.
     *
     * Pass $sortOrder to avoid a MAX(sort_order) query per file when attaching
     * many in a loop; omit it for a single attach and it resolves from the DB.
     */
    public static function attach(Post $post, UploadedFile $file, ?int $sortOrder = null): PostMedia
    {
        $isVideo = str_starts_with((string) $file->getMimeType(), 'video/');

        $path = $isVideo
            ? $file->store('uploads', 'public')
            : ImageProcessor::compress($file, 'uploads', 1600);

        return $post->media()->create([
            'path'       => $path,
            'type'       => $isVideo ? 'video' : 'image',
            'sort_order' => $sortOrder ?? $post->media()->max('sort_order') + 1,
        ]);
    }
}
