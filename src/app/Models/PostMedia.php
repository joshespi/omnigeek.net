<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PostMedia extends Model
{
    protected $fillable = ['post_id', 'path', 'type', 'sort_order'];

    protected static function booted(): void
    {
        // Fires on every delete path (Livewire, cascade-via-Post, tinker) so the
        // stored file never orphans. DB FK cascade deletes rows but skips events,
        // so Post::deleting deletes children in PHP to route them through here.
        static::deleting(fn (PostMedia $media) => Storage::disk('public')->delete($media->path));
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }
}
