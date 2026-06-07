<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'body', 'youtube_id', 'published_at'];

    protected $casts = ['view_count' => 'integer', 'published_at' => 'datetime'];

    protected static function booted(): void
    {
        // Delete children in PHP (not via DB FK cascade) so PostMedia::deleting
        // fires for each and removes its stored file. Covers user-cascade deletes too.
        static::deleting(fn (Post $post) => $post->media->each->delete());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class)->orderBy('sort_order');
    }

    public function scopeWithFeedRelations(Builder $query): Builder
    {
        return $query->with('user', 'categories', 'tags', 'media');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('published_at')->orWhere('published_at', '<=', now());
        });
    }

    public function displayDate(): \Illuminate\Support\Carbon
    {
        return $this->published_at ?? $this->created_at;
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('title', 'like', $like)
              ->orWhere('body', 'like', $like)
              ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', $like));
        });
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function canDelete(?User $user): bool
    {
        return $user && ($this->user_id === $user->id || $user->isAdmin());
    }

    public function canEdit(?User $user): bool
    {
        return $user && $this->user_id === $user->id;
    }

    public function preview(int $limit): ?string
    {
        return $this->title ?: ($this->body ? str($this->body)->limit($limit)->toString() : null);
    }

    public static function parseYoutubeId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{11})~', $url, $m)) {
            return $m[1];
        }

        return preg_match('~^[\w-]{11}$~', $url) ? $url : null;
    }
}
